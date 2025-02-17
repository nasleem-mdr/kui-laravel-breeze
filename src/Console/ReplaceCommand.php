<?php

namespace DMS\Breeze\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class ReplaceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dms-breeze:replace {stack=blade : The development stack that should be replaced (blade,react,vue)}
                            {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Replace laravel\\breeze views.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->argument('stack') === 'blade') {
            return $this->replaceBlade();
        }

        if ($this->argument('stack') === 'vue') {
            return $this->replaceVue();
        }

        if ($this->argument('stack') === 'react') {
            return $this->replaceReact();
        }
    }

    public function replaceBlade()
    {
        // NPM Packages...
        $this->updateNodePackages(function ($packages) {
            return [
                '@alpinejs/collapse' => '^3.4.2',
                'alpinejs' => '^3.4.2',
                'autoprefixer' => '^10.3.7',
                'postcss' => '^8.3.9',
                'tailwindcss' => '^2.2.16',
                'perfect-scrollbar' => '^1.5.2'
            ] + $packages;
        });

        // Views...
        (new Filesystem)->ensureDirectoryExists(resource_path('views/auth'));
        (new Filesystem)->ensureDirectoryExists(resource_path('views/layouts'));
        (new Filesystem)->ensureDirectoryExists(resource_path('views/components'));
        (new Filesystem)->ensureDirectoryExists(resource_path('views/buttons-showcase'));

        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/blade/views/auth', resource_path('views/auth'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/blade/views/layouts', resource_path('views/layouts'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/blade/views/components', resource_path('views/components'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/blade/views/buttons-showcase', resource_path('views/buttons-showcase'));

        copy(__DIR__ . '/../../stubs/blade/views/dashboard.blade.php', resource_path('views/dashboard.blade.php'));

        // Routes
        copy(__DIR__ . '/../../stubs/blade/web.php', base_path('routes/web.php'));

        // Assets
        copy(__DIR__ . '/../../stubs/blade/tailwind.config.js', base_path('tailwind.config.js'));
        copy(__DIR__ . '/../../stubs/blade/webpack.mix.js', base_path('webpack.mix.js'));
        copy(__DIR__ . '/../../stubs/blade/css/app.css', resource_path('css/app.css'));
        copy(__DIR__ . '/../../stubs/blade/js/app.js', resource_path('js/app.js'));

        // Icons
        $this->requireComposerPackages('blade-ui-kit/blade-heroicons:^1.2');

        $this->info('Breeze scaffolding replaced successfully.');
        $this->comment('Please execute the "npm install && npm run dev" command to build your assets.');
    }

    public function replaceVue()
    {
        // NPM Packages...
        $this->updateNodePackages(function ($packages) {
            return [
                '@heroicons/vue' => '^1.0.4',
                '@vueuse/core' => '^6.5.3',
                '@vue/babel-plugin-jsx' => '^1.1.0',
                'autoprefixer' => '^10.3.7',
                'postcss' => '^8.3.9',
                'tailwindcss' => '^2.2.16',
                'perfect-scrollbar' => '^1.5.2'
            ] + $packages;
        });

        // Views...
        copy(__DIR__ . '/../../stubs/vue/views/app.blade.php', resource_path('views/app.blade.php'));

        // Components + Pages...
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Components'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Composables'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Layouts'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Pages'));

        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/vue/js/Components', resource_path('js/Components'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/vue/js/Composables', resource_path('js/Composables'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/vue/js/Layouts', resource_path('js/Layouts'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/vue/js/Pages', resource_path('js/Pages'));

        // Tailwind / Webpack...
        copy(__DIR__ . '/../../stubs/vue/tailwind.config.js', base_path('tailwind.config.js'));
        copy(__DIR__ . '/../../stubs/vue/css/app.css', resource_path('css/app.css'));
        copy(__DIR__ . '/../../stubs/vue/js/app.js', resource_path('js/app.js'));
        copy(__DIR__ . '/../../stubs/vue/.babelrc', base_path('.babelrc'));
        
        $this->requireComposerPackages('blade-ui-kit/blade-heroicons:^1.2');
        $this->info('Breeze scaffolding replaced successfully.');
        $this->comment('Please execute the "npm install && npm run dev" command to build your assets.');
    }

    public function replaceReact()
    {
        $this->warn('React stack will be avilable soon.');
    }

    /**
     * Copied from https://github.com/laravel/breeze/blob/1.x/src/Console/InstallCommand.php
     * Installs the given Composer Packages into the application.
     *
     * @param  mixed  $packages
     * @return void
     */
    protected function requireComposerPackages($packages)
    {
        $composer = $this->option('composer');

        if ($composer !== 'global') {
            $command = ['php', $composer, 'require'];
        }

        $command = array_merge(
            $command ?? ['composer', 'require'],
            is_array($packages) ? $packages : func_get_args()
        );

        (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });
    }

    /**
     * Copied from https://github.com/laravel/breeze/blob/1.x/src/Console/InstallCommand.php
     * Update the "package.json" file.
     *
     * @param  callable  $callback
     * @param  bool  $dev
     * @return void
     */
    protected static function updateNodePackages(callable $callback, $dev = true)
    {
        if (!file_exists(base_path('package.json'))) {
            return;
        }

        $configurationKey = $dev ? 'devDependencies' : 'dependencies';

        $packages = json_decode(file_get_contents(base_path('package.json')), true);

        $packages[$configurationKey] = $callback(
            array_key_exists($configurationKey, $packages) ? $packages[$configurationKey] : [],
            $configurationKey
        );

        ksort($packages[$configurationKey]);

        file_put_contents(
            base_path('package.json'),
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL
        );
    }

    /**
     * Copied from https://github.com/laravel/breeze/blob/1.x/src/Console/InstallCommand.php
     * Delete the "node_modules" directory and remove the associated lock files.
     *
     * @return void
     */
    protected static function flushNodeModules()
    {
        tap(new Filesystem, function ($files) {
            $files->deleteDirectory(base_path('node_modules'));

            $files->delete(base_path('yarn.lock'));
            $files->delete(base_path('package-lock.json'));
        });
    }

    /**
     * Copied from https://github.com/laravel/breeze/blob/1.x/src/Console/InstallCommand.php
     * Replace a given string within a given file.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $path
     * @return void
     */
    protected function replaceInFile($search, $replace, $path)
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }
}
