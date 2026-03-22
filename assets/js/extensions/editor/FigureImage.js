import { Node, mergeAttributes } from '@tiptap/core'
import { NodeSelection } from '@tiptap/pm/state'
import { FigureImageView } from './FigureImageView.js'

if (!window.__tiptapNodeSelection) {
    window.__tiptapNodeSelection = NodeSelection
}

export const FigureImage = Node.create({
    name: 'figureImage',

    group: 'block',

    atom: true,

    draggable: true,

    selectable: true,

    isolating: true,

    addAttributes() {
        return {
            src: {
                default: null,
                parseHTML: (element) => {
                    const img = element.querySelector('img')
                    return img?.getAttribute('src') || null
                },
            },
            alt: {
                default: '',
                parseHTML: (element) => {
                    const img = element.querySelector('img')
                    return img?.getAttribute('alt') || ''
                },
            },
            title: {
                default: '',
                parseHTML: (element) => {
                    const img = element.querySelector('img')
                    return img?.getAttribute('title') || ''
                },
            },
            caption: {
                default: '',
                parseHTML: (element) => {
                    const figcaption = element.querySelector('figcaption')
                    return figcaption?.textContent?.trim() || ''
                },
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'figure[data-type="figure-image"]',
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        const { src, alt, title, caption } = HTMLAttributes

        const figureAttrs = mergeAttributes({
            'data-type': 'figure-image',
            class: 'article-figure',
        })

        if (caption && caption.trim() !== '') {
            return [
                'figure',
                figureAttrs,
                ['img', { src, alt, title }],
                ['figcaption', {}, caption],
            ]
        }

        return [
            'figure',
            figureAttrs,
            ['img', { src, alt, title }],
        ]
    },

    addNodeView() {
        return ({ node, view, getPos }) => {
            return new FigureImageView(node, view, getPos)
        }
    },

    addCommands() {
        return {
            setFigureImage:
                (options) =>
                    ({ commands }) => {
                        return commands.insertContent({
                            type: this.name,
                            attrs: {
                                src: options.src,
                                alt: options.alt || '',
                                title: options.title || '',
                                caption: options.caption || '',
                            },
                        })
                    },
        }
    },
})
