import jstin from './lib/subscript-9.0.0/justin.min.js'
{
	const evaluateConditions = form => {
		const fields = form.querySelectorAll(`[data-yf-condition]`)
		if (!fields.length) return
		let data = Object.fromEntries(new FormData(form))
		let dataName = form.dataset.yfForm
		fields.forEach(field => {
			const cond = field.dataset.yfCondition
			if (!cond) return
			const inputs = field.querySelectorAll('[data-yf-control]')
			if (jstin(cond)({
				value: fId => data[`tx_shape_form[${dataName}][${fId}]`] ?? null,
				formData: str => data[`tx_shape_form[${dataName}]${str}`] ?? null
			})) {
				field.classList.remove('--hidden')
				inputs.forEach(inp => inp.disabled = false)
			} else {
				field.classList.add('--hidden')
				inputs.forEach(inp => inp.disabled = true)
			}
		})
	}
	const processNode = el => {
		const form = el.closest('[data-yf-form]') ?? el.querySelector('[data-yf-form]')
		el.querySelectorAll('[data-yf-control]').forEach(field => {
			field.addEventListener('change', () => evaluateConditions(form))
		})
		window.requestAnimationFrame(() => evaluateConditions(form))
	}
	window.__t3_tx_shape.processors.conditionalFields = processNode
	document.querySelectorAll('[data-yf-form]').forEach(form => processNode(form))
}