<template>
    <component
        :is="Tag"
        v-if="href"
        :class="[
            'p-2 flex items-center gap-2 rounded-md transition-colors',
            {
                'text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-dark-eval-2':
                    !active,
                'text-white bg-purple-500 shadow-lg hover:bg-purple-600':
                    active,
            },
        ]"
    >
        <slot name="icon">
            <EmptyCircleIcon aria-hidden="true" class="flex-shrink-0 w-6 h-6" />
        </slot>

        <span
            class="text-base font-medium"
            v-show="sidebarState.isOpen || sidebarState.isHovered"
        >
            {{ title }}
        </span>
    </component>
    <button
        v-else
        type="button"
        :class="[
            'p-2 w-full flex items-center gap-2 rounded-md transition-colors',
            {
                'text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-dark-eval-2':
                    !active,
                'text-white bg-purple-500 shadow-lg hover:bg-purple-600':
                    active,
            },
        ]"
    >
        <slot name="icon">
            <EmptyCircleIcon aria-hidden="true" class="flex-shrink-0 w-6 h-6" />
        </slot>

        <span
            class="text-base font-medium"
            v-show="sidebarState.isOpen || sidebarState.isHovered"
        >
            {{ title }}
        </span>
        <slot name="arrow" />
    </button>
</template>

<script>
import { Link } from '@inertiajs/inertia-vue3'
import { sidebarState } from '@/Composables'

import { EmptyCircleIcon } from '@/Components/Icons/outline.jsx'

export default {
    components: {
        EmptyCircleIcon,
    },
    props: {
        href: {
            type: String,
            required: false,
        },
        active: {
            type: Boolean,
            default: false,
        },
        title: {
            type: String,
            required: true,
        },
        icon: {
            required: false,
        },
        withArrow: {
            default: false,
        },
    },
    setup(props) {
        const { href, active, title, icon: Icon, withArrow } = props

        const Tag = !href ? 'button' : href && !external ? 'a' : Link

        return {
            sidebarState,
            Tag,
        }
    },
}
</script>
