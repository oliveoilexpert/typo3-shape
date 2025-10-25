{
	const addButtonHandler = e => {
		const btn = e.currentTarget

		if (!btn.disabled) {
			btn.disabled = true
			setTimeout(() => btn.disabled = false, 500)
		}

		const container = document.getElementById(btn.dataset.shapeRepeatableAdd)
		if (!container) return

		const tmpl = container.querySelector('template')
		if (!tmpl) return

		const i = tmpl.dataset.iteration
		const clone = tmpl.content.cloneNode(true)

		clone.querySelectorAll('[data-shape-condition]').forEach(wrap => {
			const cond = wrap.dataset.shapeCondition
			if (cond) {
				wrap.setAttribute('data-shape-condition', cond.replaceAll('[__INDEX]', `[${i}]`))
			}
		})

		clone.querySelectorAll('input, textarea, select').forEach(input => {
			input.name = input.name.replaceAll('[__INDEX]', `[${i}]`)
			const newId = input.id.replaceAll('[__INDEX]', `[${i}]`)
			clone.querySelector(`label[for="${input.id}"]`)?.setAttribute('for', newId)
			input.id = newId
		})

		const repeatableItem = clone.querySelector('[data-shape-repeatable-item]')
		if (repeatableItem) {
			repeatableItem.dataset.shapeRepeatableItem = i
		}

		const nodes = [...clone.childNodes]
		container.appendChild(clone)

		nodes.forEach(node => {
			if (node.nodeType === Node.ELEMENT_NODE) {
				document.dispatchEvent(new CustomEvent('shape:connect', {
					detail: {element: node}
				}))
			}
		})

		tmpl.dataset.iteration = (parseInt(i) + 1).toString()
	}

	const removeButtonHandler = e => {
		e.currentTarget.closest('[data-shape-repeatable-item]')?.remove()
	}

	const connectElement = el => {
		el.querySelectorAll('[data-shape-repeatable-item]').forEach(item => {
			item.querySelectorAll('[data-shape-condition*="[__INDEX]"]').forEach(field => {
				field.setAttribute(
					'data-shape-condition',
					field.dataset.shapeCondition.replaceAll('[__INDEX]', `[${item.dataset.shapeRepeatableItem}]`)
				)
			})
		})

		if (!document.__shapeRepeatableDelegation) {
			document.__shapeRepeatableDelegation = true

			document.addEventListener('click', e => {
				if (e.target.matches('[data-shape-repeatable-add]')) {
					addButtonHandler(e)
				}
				if (e.target.matches('[data-shape-repeatable-remove]')) {
					removeButtonHandler(e)
				}
			})
		}
	}

	document.addEventListener('shape:connect', e => connectElement(e.detail.element))
	document.querySelectorAll('[data-shape-form]').forEach(form => connectElement(form))
}