{
	const processNode = el => {
		el.querySelectorAll('[data-shape-validation-message]').forEach(input => {
			input.addEventListener('invalid', () => {
				input.setCustomValidity(input.dataset.shapeValidationMessage)
			})
			input.addEventListener('change', () => {
				input.setCustomValidity('')
			})
		})
	}
	window.__t3_tx_shape.processors.validityMessages = processNode
	document.querySelectorAll('[data-shape-form]').forEach(form => processNode(form))
}