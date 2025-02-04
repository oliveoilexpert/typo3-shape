{
	const addButtonHandler = e => {
		const btn = e.currentTarget
		if (!btn.disabled) {
			btn.setAttribute('disabled', '')
			setTimeout(() => btn.removeAttribute('disabled'), 500)
		}
		const container = document.getElementById(btn.dataset.shapeRepeatableAdd)
		if (!container) return
		const tmpl = container.querySelector('template')
		if (!tmpl) return
		const i = tmpl.dataset.iteration
		let clone = tmpl.content.cloneNode(true)
		clone.querySelectorAll('[data-shape-condition]').forEach(wrap => {
			const cond = wrap.dataset.shapeCondition
			if (!cond) return
			wrap.setAttribute('data-shape-condition', cond.replaceAll(`[__INDEX]`, `[${i}]`))
		})
		clone.querySelectorAll('input, textarea, select').forEach(input => {
			input.name = input.name.replaceAll(`[__INDEX]`, `[${i}]`)
			const newId = input.id + '-' + i
			clone.querySelector(`label[for="${input.id}"]`)?.setAttribute('for', newId)
			input.id = newId
		});
		let nodes = [...clone.childNodes]
		container.appendChild(clone)
		nodes.forEach(node => {
			if (node.nodeType == Node.ELEMENT_NODE) window.__tx_shape.process(node)
		})
		tmpl.dataset.iteration = (parseInt(i) + 1).toString()
	}
	const removeButtonHandler = e => {
		e.currentTarget.closest('[data-shape-repeatable-item]')?.remove()
	}
	const processNode = el => {
		el.querySelectorAll('[data-shape-repeatable-add]').forEach(btn => {
			btn.addEventListener('click', addButtonHandler)
		})
		el.querySelectorAll('[data-shape-repeatable-remove]').forEach(btn => {
			btn.addEventListener('click', removeButtonHandler)
		})
	}
	window.__tx_shape.processors.repeatableContainer = processNode
	document.querySelectorAll('[data-shape-form]').forEach(form => processNode(form))
}