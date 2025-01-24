{
	const addButtonHandler = e => {
		const button = e.currentTarget
		if (!button.disabled) {
			button.setAttribute('disabled', '')
			setTimeout(() => {
				button.removeAttribute('disabled')
			}, 500)
		}
		const container = document.getElementById(button.dataset.shapeRepeatableAdd)
		if (!container) return
		const template = container.querySelector('template')
		if (!template) return
		const index = template.dataset.iteration
		const nextIndex = (parseInt(index) + 1).toString()

		const clone = template.content.cloneNode(true)
		clone.querySelectorAll('input, textarea, select').forEach((input) => {
			input.name = input.name.replaceAll(`[__INDEX]`, `[${index}]`)
			const newId = input.id + '-' + index
			clone.querySelector(`label[for="${input.id}"]`)?.setAttribute('for', newId)
			input.id = newId
		});
		processNode(clone)
		container.appendChild(clone)
		template.dataset.iteration = nextIndex
	}

	const removeButtonHandler = e => {
		const button = e.currentTarget
		const item = button.closest('[data-shape-repeatable-item]')
		item.remove()
	}

	const processNode = el => {
		el.querySelectorAll('[data-shape-repeatable-add]').forEach(button => {
			button.addEventListener('click', addButtonHandler)
		})
		el.querySelectorAll('[data-shape-repeatable-remove]').forEach(button => {
			button.addEventListener('click', removeButtonHandler)
		})
	}

	if (!window.__tx_shape) {
		window.__tx_shape = {}
	}

	window.__tx_shape.repeatableContainer = {
		addButtonHandler,
		removeButtonHandler,
		processNode
	}

	document.querySelectorAll('[data-shape-form]').forEach(form => processNode(form))
}