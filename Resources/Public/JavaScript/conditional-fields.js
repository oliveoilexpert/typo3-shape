import jstin from './lib/subscript-9.0.0/justin.min.js'
{
	const evaluateConditions = form => {
		const fields = form.querySelectorAll(`[data-shape-condition]`)
		if (!fields.length) return
		let data = Object.fromEntries(new FormData(form))
		fields.forEach(field => {
			const cond = field.dataset.shapeCondition
			if (!cond) return
			const inputs = field.querySelectorAll('[data-shape-control]')
			if (jstin(cond)({
				value: fId => data[`tx_shape_form[values][${fId}]`] ?? null,
				formData: str => data['tx_shape_form[values]' + str] ?? null
			})) {
				field.classList.remove('-hidden')
				inputs.forEach(inp => inp.disabled = false)
			} else {
				field.classList.add('-hidden')
				inputs.forEach(inp => inp.disabled = true)
			}
		})
	}
	const processNode = el => {
		const form = el.closest('[data-shape-form]') ?? el.querySelector('[data-shape-form]')
		el.querySelectorAll('[data-shape-control]').forEach(field => {
			field.addEventListener('change', () => evaluateConditions(form))
		})
		evaluateConditions(form)
	}
	window.__tx_shape.processors.conditionalFields = processNode
	document.querySelectorAll('[data-shape-form]').forEach(form => processNode(form))
}