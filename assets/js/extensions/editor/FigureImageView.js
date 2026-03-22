export class FigureImageView {
    constructor(node, view, getPos) {
        this.node = node
        this.view = view
        this.getPos = getPos

        // основной контейнер
        this.dom = document.createElement('figure')
        this.dom.className = 'article-figure'
        this.dom.setAttribute('data-type', 'figure-image')

        // обёртка (для кнопки удаления)
        this.wrapper = document.createElement('div')
        this.wrapper.className = 'editor-figure-wrapper'

        // изображение
        this.image = document.createElement('img')
        this.image.src = node.attrs.src || ''
        this.image.alt = node.attrs.alt || ''
        this.image.title = node.attrs.title || ''

        // кнопка удаления
        this.deleteButton = document.createElement('button')
        this.deleteButton.type = 'button'
        this.deleteButton.className = 'editor-figure-delete-btn'
        this.deleteButton.setAttribute('aria-label', 'Удалить изображение')
        this.deleteButton.textContent = '×'

        // поле подписи
        this.captionInput = document.createElement('input')
        this.captionInput.type = 'text'
        this.captionInput.className = 'editor-figure-caption-input'
        this.captionInput.placeholder = 'Подпись к изображению'
        this.captionInput.value = node.attrs.caption || ''

        // сборка DOM
        this.wrapper.appendChild(this.image)
        this.wrapper.appendChild(this.deleteButton)

        this.dom.appendChild(this.wrapper)
        this.dom.appendChild(this.captionInput)

        // бинды
        this.handleImageClick = this.handleImageClick.bind(this)
        this.handleCaptionMouseDown = this.handleCaptionMouseDown.bind(this)
        this.handleCaptionClick = this.handleCaptionClick.bind(this)
        this.handleCaptionInput = this.handleCaptionInput.bind(this)
        this.handleDeleteClick = this.handleDeleteClick.bind(this)

        // события
        this.image.addEventListener('click', this.handleImageClick)
        this.captionInput.addEventListener('mousedown', this.handleCaptionMouseDown)
        this.captionInput.addEventListener('click', this.handleCaptionClick)
        this.captionInput.addEventListener('input', this.handleCaptionInput)
        this.deleteButton.addEventListener('click', this.handleDeleteClick)
    }

    handleImageClick() {
        const pos = this.getPos()

        if (typeof pos !== 'number') {
            return
        }

        const { state } = this.view
        const tr = state.tr.setSelection(
            window.__tiptapNodeSelection.create(state.doc, pos)
        )

        this.view.dispatch(tr)
        this.view.focus()
    }

    handleCaptionMouseDown(event) {
        event.stopPropagation()
    }

    handleCaptionClick(event) {
        event.stopPropagation()
    }

    handleCaptionInput(event) {
        const caption = event.target.value || ''
        const pos = this.getPos()

        if (typeof pos !== 'number') {
            return
        }

        const tr = this.view.state.tr.setNodeMarkup(pos, undefined, {
            ...this.node.attrs,
            caption,
            alt: caption,
            title: caption,
        })

        this.view.dispatch(tr)
    }

    handleDeleteClick(event) {
        event.preventDefault()
        event.stopPropagation()

        const pos = this.getPos()

        if (typeof pos !== 'number') {
            return
        }

        const tr = this.view.state.tr.delete(pos, pos + this.node.nodeSize)
        this.view.dispatch(tr)
        this.view.focus()
    }

    update(node) {
        if (node.type.name !== 'figureImage') {
            return false
        }

        this.node = node

        const nextSrc = node.attrs.src || ''

        if (this.image.src !== nextSrc) {
            this.image.src = nextSrc
        }

        this.image.alt = node.attrs.alt || ''
        this.image.title = node.attrs.title || ''

        // не перезаписываем, если пользователь печатает
        if (document.activeElement !== this.captionInput) {
            this.captionInput.value = node.attrs.caption || ''
        }

        return true
    }

    selectNode() {
        this.dom.classList.add('ProseMirror-selectednode')
    }

    deselectNode() {
        this.dom.classList.remove('ProseMirror-selectednode')
    }

    stopEvent(event) {
        return (
            event.target === this.captionInput ||
            event.target === this.deleteButton
        )
    }

    ignoreMutation(mutation) {
        return mutation.target === this.captionInput
    }

    destroy() {
        this.image.removeEventListener('click', this.handleImageClick)
        this.captionInput.removeEventListener('mousedown', this.handleCaptionMouseDown)
        this.captionInput.removeEventListener('click', this.handleCaptionClick)
        this.captionInput.removeEventListener('input', this.handleCaptionInput)
        this.deleteButton.removeEventListener('click', this.handleDeleteClick)
    }
}
