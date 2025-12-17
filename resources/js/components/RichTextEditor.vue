<script setup lang="ts">
import { watch, onBeforeUnmount } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Underline from '@tiptap/extension-underline';
import Placeholder from '@tiptap/extension-placeholder';
import { Button } from '@/components/ui/button';
import {
    Bold,
    Italic,
    Underline as UnderlineIcon,
    List,
    ListOrdered,
    Heading2,
    Link as LinkIcon,
    Undo,
    Redo,
} from 'lucide-vue-next';

const props = defineProps<{
    modelValue: string;
    placeholder?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit,
        Underline,
        Link.configure({
            openOnClick: false,
            autolink: true,
            defaultProtocol: 'https',
            protocols: ['http', 'https'],
            isAllowedUri: (url, ctx) => {
                try {
                    const parsedUrl = url.includes(':')
                        ? new URL(url)
                        : new URL(`${ctx.defaultProtocol}://${url}`);

                    if (!ctx.defaultValidate(parsedUrl.href)) {
                        return false;
                    }

                    const disallowedProtocols = ['ftp', 'file', 'mailto'];
                    const protocol = parsedUrl.protocol.replace(':', '');

                    if (disallowedProtocols.includes(protocol)) {
                        return false;
                    }

                    const allowedProtocols = ctx.protocols.map((p) =>
                        typeof p === 'string' ? p : p.scheme,
                    );

                    if (!allowedProtocols.includes(protocol)) {
                        return false;
                    }

                    const disallowedDomains = [
                        'example-phishing.com',
                        'malicious-site.net',
                    ];
                    const domain = parsedUrl.hostname;

                    if (disallowedDomains.includes(domain)) {
                        return false;
                    }

                    return true;
                } catch {
                    return false;
                }
            },
            shouldAutoLink: (url) => {
                try {
                    const parsedUrl = url.includes(':')
                        ? new URL(url)
                        : new URL(`https://${url}`);

                    const disallowedDomains = [
                        'example-no-autolink.com',
                        'another-no-autolink.com',
                    ];
                    const domain = parsedUrl.hostname;

                    return !disallowedDomains.includes(domain);
                } catch {
                    return false;
                }
            },
            HTMLAttributes: {
                class: 'text-blue-600 dark:text-blue-400 underline hover:text-blue-800 dark:hover:text-blue-300',
            },
        }),
        Placeholder.configure({
            placeholder: props.placeholder || 'Write your content here...',
        }),
    ],
    editorProps: {
        attributes: {
            class: 'prose dark:prose-invert prose-li:leading-7 prose-p:m-2 focus:outline-none max-w-none min-h-[200px] p-4',
        },
        handleDOMEvents: {
            click: (view, event) => {
                const target = event.target as HTMLElement | null;
                const link = target?.closest('a[href]');

                // Only intercept when editor is editable (so in read-only mode links still work)
                if (link && view.editable) {
                    event.preventDefault();
                    // Optionally: keep this click "handled"
                    return true;
                }

                return false;
            },
        },
    },
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML());
    },
});

watch(
    () => props.modelValue,
    (value) => {
        const isSame = editor.value?.getHTML() === value;
        if (isSame) {
            return;
        }
        editor.value?.commands.setContent(value, false);
    },
);

const setLink = () => {
    if (!editor.value) return;

    const previousUrl = editor.value.getAttributes('link').href;
    const url = window.prompt('URL', previousUrl);

    if (url === null) {
        return;
    }

    if (url === '') {
        editor.value.chain().focus().extendMarkRange('link').unsetLink().run();
        return;
    }

    editor.value
        .chain()
        .focus()
        .extendMarkRange('link')
        .setLink({ href: url })
        .run();
};

onBeforeUnmount(() => {
    editor.value?.destroy();
});
</script>

<template>
    <div
        v-if="editor"
        class="rounded-md border border-input bg-background focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2"
    >
        <div
            class="flex flex-wrap items-center gap-1 border-b border-input bg-muted/50 p-2"
        >
            <Button
                type="button"
                variant="ghost"
                size="sm"
                @click="editor.chain().focus().toggleBold().run()"
                :class="{ 'bg-muted': editor.isActive('bold') }"
            >
                <Bold class="h-4 w-4" />
            </Button>
            <Button
                type="button"
                variant="ghost"
                size="sm"
                @click="editor.chain().focus().toggleItalic().run()"
                :class="{ 'bg-muted': editor.isActive('italic') }"
            >
                <Italic class="h-4 w-4" />
            </Button>
            <Button
                type="button"
                variant="ghost"
                size="sm"
                @click="editor.chain().focus().toggleUnderline().run()"
                :class="{ 'bg-muted': editor.isActive('underline') }"
            >
                <UnderlineIcon class="h-4 w-4" />
            </Button>
            <div class="mx-1 h-6 w-px bg-border" />
            <Button
                type="button"
                variant="ghost"
                size="sm"
                @click="
                    editor.chain().focus().toggleHeading({ level: 2 }).run()
                "
                :class="{
                    'bg-muted': editor.isActive('heading', { level: 2 }),
                }"
            >
                <Heading2 class="h-4 w-4" />
            </Button>
            <div class="mx-1 h-6 w-px bg-border" />
            <Button
                type="button"
                variant="ghost"
                size="sm"
                @click="editor.chain().focus().toggleBulletList().run()"
                :class="{ 'bg-muted': editor.isActive('bulletList') }"
            >
                <List class="h-4 w-4" />
            </Button>
            <Button
                type="button"
                variant="ghost"
                size="sm"
                @click="editor.chain().focus().toggleOrderedList().run()"
                :class="{ 'bg-muted': editor.isActive('orderedList') }"
            >
                <ListOrdered class="h-4 w-4" />
            </Button>
            <div class="mx-1 h-6 w-px bg-border" />
            <Button
                type="button"
                variant="ghost"
                size="sm"
                @click="setLink"
                :class="{ 'bg-muted': editor.isActive('link') }"
            >
                <LinkIcon class="h-4 w-4" />
            </Button>
            <div class="mx-1 h-6 w-px bg-border" />
            <Button
                type="button"
                variant="ghost"
                size="sm"
                @click="editor.chain().focus().undo().run()"
                :disabled="!editor.can().undo()"
            >
                <Undo class="h-4 w-4" />
            </Button>
            <Button
                type="button"
                variant="ghost"
                size="sm"
                @click="editor.chain().focus().redo().run()"
                :disabled="!editor.can().redo()"
            >
                <Redo class="h-4 w-4" />
            </Button>
        </div>
        <EditorContent :editor="editor" class="editor-content" />
    </div>
</template>

<style>
.editor-content .ProseMirror {
    outline: none;
}

.editor-content .ProseMirror p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    float: left;
    color: #adb5bd;
    pointer-events: none;
    height: 0;
}
</style>
