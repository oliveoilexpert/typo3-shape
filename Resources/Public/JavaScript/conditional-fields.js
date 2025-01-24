{
	if (!window.__tx_shape) {
		window.__tx_shape = {}
	}

	console.log('conditional fields')
	let formData = {}
	const getValue = fieldId => {
		return formData[`tx_shape_form[values][${fieldId}]`] ?? null
	}

	console.log(window.__tx_shape)
	window.__tx_shape.jexl.addFunction('value', fieldId => {
		return getValue(fieldId)
	})

	const evaluateConditions = form => {
		const conditionalFields = form.querySelectorAll(`[data-shape-condition]`)
		if (!conditionalFields.length) return

		formData = Object.fromEntries(new FormData(form))
		conditionalFields.forEach(field => {
			const condition = field.dataset.shapeCondition
			const inputs = field.querySelectorAll('[data-shape-field]')
			if (window.__tx_shape.jexl.evalSync(condition)) {
				field.style.display = ''
				inputs.forEach(input => {
					input.disabled = false
				})
			} else {
				field.style.display = 'none'
				inputs.forEach(input => {
					input.disabled = true
				})
			}
		})
	}

	const processNode = el => {
		const form = el.closest('form') ?? el.querySelector('form')
		el.querySelectorAll('[data-shape-field]').forEach(button => {
			button.addEventListener('onchange', evaluateConditions(form))
		})
	}

	window.__tx_shape.conditionalFields = {
		processNode,
		evaluateConditions
	}

	document.querySelectorAll('[data-shape-form]').forEach(form => processNode(form))
}