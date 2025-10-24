{
	const addButtonHandler = e => {
		const btn = e.currentTarget
		if (!btn.disabled) {
			btn.setAttribute('disabled', '')
			setTimeout(() => btn.removeAttribute('disabled'), 500)
		}
		const container = document.getElementById(btn.dataset.yfRepeatableAdd)
		if (!container) return
		const tmpl = container.querySelector('template')
		if (!tmpl) return
		const i = tmpl.dataset.iteration
		let clone = tmpl.content.cloneNode(true)
		clone.querySelectorAll('[data-shape-condition]').forEach(wrap => {
			const cond = wrap.dataset.yfCondition
			if (!cond) return
			wrap.setAttribute('data-shape-condition', cond.replaceAll(`[__INDEX]`, `[${i}]`))
		})
		clone.querySelectorAll('input, textarea, select').forEach(input => {
			input.name = input.name.replaceAll(`[__INDEX]`, `[${i}]`)
			const newId = input.id.replaceAll(`[__INDEX]`, `[${i}]`)
			//const newId = input.id + '-' + i
			clone.querySelector(`label[for="${input.id}"]`)?.setAttribute('for', newId)
			input.id = newId
		});
		const repeatableItem = clone.querySelector('[data-shape-repeatable-item]')
		if (repeatableItem) {
			repeatableItem.dataset.yfRepeatableItem = i
		}
		let nodes = [...clone.childNodes]
		container.appendChild(clone)
		nodes.forEach(node => {
			if (node.nodeType == Node.ELEMENT_NODE) window.__t3_tx_shape.process(node)
		})
		tmpl.dataset.iteration = (parseInt(i) + 1).toString()
	}
	const removeButtonHandler = e => {
		e.currentTarget.closest('[data-shape-repeatable-item]')?.remove()
	}
	const processNode = el => {
		el.querySelectorAll('[data-shape-repeatable-item]').forEach(repeatableItem => {
			repeatableItem.querySelectorAll('[data-shape-condition*="[__INDEX]"]').forEach(field => {
				field.setAttribute('data-shape-condition', field.dataset.yfCondition.replaceAll(`[__INDEX]`, `[${repeatableItem.dataset.yfRepeatableItem}]`))
			})
		})
		el.querySelectorAll('[data-shape-repeatable-add]').forEach(btn => {
			btn.addEventListener('click', addButtonHandler)
		})
		el.querySelectorAll('[data-shape-repeatable-remove]').forEach(btn => {
			btn.addEventListener('click', removeButtonHandler)
		})
	}
	window.__t3_tx_shape.processors.repeatableContainer = processNode
	document.querySelectorAll('[data-shape-form]').forEach(form => processNode(form))
}