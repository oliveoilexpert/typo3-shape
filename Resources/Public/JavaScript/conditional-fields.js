{
	if (!window.__tx_shape) {
		window.__tx_shape = {
			processors: {}
		}
	}

	let formData = {}
	const getValue = fieldId => {
		return formData[`tx_shape_form[values][${fieldId}]`] ?? null
	}

	window.__tx_shape.jexl.addFunction('value', fieldId => {
		return getValue(fieldId)
	})

	const evaluateConditions = form => {
		const conditionalFields = form.querySelectorAll(`[data-shape-condition]`)
		if (!conditionalFields.length) return

		formData = Object.fromEntries(new FormData(form))
		conditionalFields.forEach(field => {
			const condition = field.dataset.shapeCondition
			if (!condition) return
			const inputs = field.querySelectorAll('[data-shape-field]')
			if (window.__tx_shape.jexl.evalSync(condition)) {
				field.classList.remove('-hidden')
				inputs.forEach(input => {
					input.disabled = false
				})
			} else {
				field.classList.add('-hidden')
				inputs.forEach(input => {
					input.disabled = true
				})
			}
		})
	}

	const processNode = el => {
		const form = el.closest('[data-shape-form]') ?? el.querySelector('[data-shape-form]')
		el.querySelectorAll('[data-shape-field]').forEach(field => {
			field.addEventListener('change', () => evaluateConditions(form))
		})
		evaluateConditions(form)
	}


	window.__tx_shape.processors.conditionalFields = processNode
	document.querySelectorAll('[data-shape-form]').forEach(form => processNode(form))
}