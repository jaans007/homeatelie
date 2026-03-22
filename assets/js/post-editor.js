import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Placeholder from '@tiptap/extension-placeholder'
import Link from '@tiptap/extension-link'
import Underline from '@tiptap/extension-underline'
import TextAlign from '@tiptap/extension-text-align'
import Youtube from '@tiptap/extension-youtube'
import { FigureImage } from './extensions/editor/FigureImage.js'

document.addEventListener('DOMContentLoaded', () => {
    initPostEditor()
    initPostHeroPreview()
})

function initPostEditor() {
    const editorElement = document.getElementById('postEditor')
    const textarea = document.querySelector('.js-post-content')
    const inlineImageInput = document.getElementById('postInlineImageInput')

    if (!editorElement || !textarea) {
        return
    }

    const editor = new Editor({
        element: editorElement,
        extensions: [
            StarterKit.configure({
                link: false,
                underline: false,
            }),
            Placeholder.configure({
                placeholder: 'Начните писать статью...',
            }),
            Link.configure({
                openOnClick: false,
                autolink: true,
                defaultProtocol: 'https',
                HTMLAttributes: {
                    rel: 'noopener noreferrer nofollow',
                    target: '_blank',
                },
            }),
            Underline,
            TextAlign.configure({
                types: ['heading', 'paragraph'],
            }),
            Youtube.configure({
                controls: true,
                nocookie: true,
                allowFullscreen: true,
                width: 840,
                height: 472,
            }),
            FigureImage,
        ],
        content: textarea.value || '<p></p>',
        onUpdate: ({ editor: currentEditor }) => {
            textarea.value = currentEditor.getHTML()
        },
    })

    const buttons = document.querySelectorAll('.tiptap-btn')

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            const action = button.dataset.action

            switch (action) {
                case 'bold':
                    editor.chain().focus().toggleBold().run()
                    break

                case 'italic':
                    editor.chain().focus().toggleItalic().run()
                    break

                case 'strike':
                    editor.chain().focus().toggleStrike().run()
                    break

                case 'link': {
                    const previousUrl = editor.getAttributes('link').href || ''
                    const url = window.prompt('Введите ссылку:', previousUrl)

                    if (url === null) {
                        break
                    }

                    const trimmedUrl = url.trim()

                    if (trimmedUrl === '') {
                        editor.chain().focus().extendMarkRange('link').unsetLink().run()
                        break
                    }

                    editor
                        .chain()
                        .focus()
                        .extendMarkRange('link')
                        .setLink({ href: trimmedUrl })
                        .run()
                    break
                }

                case 'youtube': {
                    const url = window.prompt('Вставьте ссылку на YouTube видео')

                    if (url === null) {
                        break
                    }

                    const trimmedUrl = url.trim()

                    if (trimmedUrl === '') {
                        break
                    }

                    editor
                        .chain()
                        .focus()
                        .setYoutubeVideo({
                            src: trimmedUrl,
                            width: 840,
                            height: 472,
                        })
                        .run()
                    break
                }

                case 'image':
                case 'figure-image':
                    inlineImageInput?.click()
                    break

                case 'h2':
                    editor.chain().focus().toggleHeading({ level: 2 }).run()
                    break

                case 'h3':
                    editor.chain().focus().toggleHeading({ level: 3 }).run()
                    break

                case 'bulletList':
                    editor.chain().focus().toggleBulletList().run()
                    break

                case 'orderedList':
                    editor.chain().focus().toggleOrderedList().run()
                    break

                case 'blockquote':
                    editor.chain().focus().toggleBlockquote().run()
                    break

                case 'undo':
                    editor.chain().focus().undo().run()
                    break

                case 'redo':
                    editor.chain().focus().redo().run()
                    break

                case 'underline':
                    editor.chain().focus().toggleUnderline().run()
                    break

                case 'alignLeft':
                    editor.chain().focus().setTextAlign('left').run()
                    break

                case 'alignCenter':
                    editor.chain().focus().setTextAlign('center').run()
                    break

                case 'alignRight':
                    editor.chain().focus().setTextAlign('right').run()
                    break
            }

            updateToolbarState()
        })
    })

    if (inlineImageInput) {
        inlineImageInput.addEventListener('change', async (event) => {
            const file = event.target.files?.[0]

            if (!file) {
                return
            }

            if (!file.type.startsWith('image/')) {
                alert('Можно выбрать только изображение')
                inlineImageInput.value = ''
                return
            }

            const formData = new FormData()
            formData.append('image', file)

            try {
                const response = await fetch('/editor/upload-image', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })

                const result = await response.json()

                if (!response.ok || !result.url) {
                    throw new Error(result.message || 'Ошибка загрузки изображения')
                }

                editor
                    .chain()
                    .focus()
                    .setFigureImage({
                        src: result.url,
                        alt: '',
                        title: '',
                        caption: '',
                    })
                    .run()

                textarea.value = editor.getHTML()
            } catch (error) {
                console.error(error)
                alert(error.message || 'Ошибка загрузки')
            } finally {
                inlineImageInput.value = ''
            }
        })
    }

    function updateToolbarState() {
        buttons.forEach((button) => {
            const action = button.dataset.action
            let isActive = false

            switch (action) {
                case 'bold':
                    isActive = editor.isActive('bold')
                    break
                case 'italic':
                    isActive = editor.isActive('italic')
                    break
                case 'underline':
                    isActive = editor.isActive('underline')
                    break
                case 'strike':
                    isActive = editor.isActive('strike')
                    break
                case 'link':
                    isActive = editor.isActive('link')
                    break
                case 'youtube':
                    isActive = editor.isActive('youtube')
                    break
                case 'h2':
                    isActive = editor.isActive('heading', { level: 2 })
                    break
                case 'h3':
                    isActive = editor.isActive('heading', { level: 3 })
                    break
                case 'bulletList':
                    isActive = editor.isActive('bulletList')
                    break
                case 'orderedList':
                    isActive = editor.isActive('orderedList')
                    break
                case 'blockquote':
                    isActive = editor.isActive('blockquote')
                    break
                case 'alignLeft':
                    isActive = editor.isActive({ textAlign: 'left' })
                    break
                case 'alignCenter':
                    isActive = editor.isActive({ textAlign: 'center' })
                    break
                case 'alignRight':
                    isActive = editor.isActive({ textAlign: 'right' })
                    break
            }

            button.classList.toggle('is-active', isActive)
        })
    }

    editor.on('selectionUpdate', updateToolbarState)
    editor.on('transaction', updateToolbarState)

    updateToolbarState()
}

function initPostHeroPreview() {
    const heroPreview = document.querySelector('#postHeroPreview')
    const heroUpload = document.querySelector('#postHeroUpload')
    const imageInput = document.querySelector('.js-post-image-input')

    if (!heroPreview || !heroUpload || !imageInput) {
        return
    }

    heroUpload.addEventListener('click', () => {
        imageInput.click()
    })

    imageInput.addEventListener('change', (event) => {
        const file = event.target.files?.[0]

        if (!file) {
            return
        }

        if (!file.type.startsWith('image/')) {
            alert('Выберите изображение')
            imageInput.value = ''
            return
        }

        const oldPreviewImage = heroPreview.querySelector('.post-create-cover-image')

        if (oldPreviewImage) {
            oldPreviewImage.remove()
        }

        const previewUrl = URL.createObjectURL(file)
        const previewImage = document.createElement('img')

        previewImage.src = previewUrl
        previewImage.alt = 'Превью обложки'
        previewImage.className = 'post-create-cover-image'

        heroPreview.prepend(previewImage)
        heroPreview.classList.add('has-image')
    })
}
