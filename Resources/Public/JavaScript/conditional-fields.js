import jstin from './lib/subscript-9.0.0/justin.min.js'

{
	const evaluateConditions = form => {
		const fields = form.querySelectorAll('[data-shape-condition]')
		if (!fields.length) return

		const data = Object.fromEntries(new FormData(form))
		const dataName = form.dataset.yfForm

		fields.forEach(field => {
			const cond = field.dataset.yfCondition
			if (!cond) return

			const inputs = field.querySelectorAll('[data-shape-control]')
			const isVisible = jstin(cond)({
				value: fId => data[`tx_shape_form[${dataName}][${fId}]`] ?? null,
				formData: str => data[`tx_shape_form[${dataName}]${str}`] ?? null
			})

			field.classList.toggle('--hidden', !isVisible)
			inputs.forEach(inp => inp.disabled = !isVisible)
		})
	}

	const connectElement = el => {
		const form = el.closest('[data-shape-form]') ?? el.querySelector('[data-shape-form]')
		if (!form) return

		if (!form.__shapeConditionalDelegation) {
			form.__shapeConditionalDelegation = true
			form.addEventListener('change', e => {
				if (e.target.matches('[data-shape-control]')) {
					evaluateConditions(form)
				}
			})
		}

		if (el.querySelector('[data-shape-condition]')) {
			requestAnimationFrame(() => evaluateConditions(form))
		}
	}

	document.addEventListener('shape:connect', e => connectElement(e.detail.element))
	document.querySelectorAll('[data-shape-form]').forEach(form => connectElement(form))
}