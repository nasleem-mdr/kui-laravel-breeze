<template>
    <div class="flex items-center justify-between flex-shrink-0 px-3">
        <Link :href="route('dashboard')" class="inline-flex items-center gap-2">
            <span class="sr-only">K-UI</span>
            <ApplicationLogo aria-hidden="true" class="w-10 h-auto" />
        </Link>

        <Button 
            iconOnly 
            variant="secondary" 
            type="button" 
            v-slot="{ iconSizeClasses }"
            v-show="sidebarState.isOpen || sidebarState.isHovered"
            @click="sidebarState.isOpen = !sidebarState.isOpen"
            :srText="sidebarState.isOpen ? 'Close sidebar' : 'Open sidebar'"
        >
            <MenuFoldLineLeftIcon 
                aria-hidden="true" 
                v-show="sidebarState.isOpen" 
                :class="['hidden lg:block', iconSizeClasses]" />

            <MenuFoldLineRightIcon 
                aria-hidden="true"
                v-show="!sidebarState.isOpen"  
                :class="['hidden lg:block', iconSizeClasses]" />

            <XIcon 
                aria-hidden="true" 
                :class="['lg:hidden', iconSizeClasses]" />
        </Button>
    </div>
</template>

<script>
import { Link, usePage } from '@inertiajs/inertia-vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Button from '@/Components/Button.vue'
import { MenuFoldLineLeftIcon, MenuFoldLineRightIcon } from '@/Components/Icons/outline.jsx'
import { XIcon } from '@heroicons/vue/outline'
import { sidebarState } from '@/Composables'

export default {
    components: {
        Link,
        ApplicationLogo,
        Button,
        MenuFoldLineLeftIcon,
        MenuFoldLineRightIcon,
        XIcon
    },

    setup(){
        return {
            sidebarState
        }
    }
}
</script>
