{
	const processNode = el => {
		el.querySelectorAll('[data-yf-validation-message]').forEach(input => {
			input.addEventListener('invalid', () => {
				input.setCustomValidity(input.dataset.yfValidationMessage)
			})
			input.addEventListener('change', () => {
				input.setCustomValidity('')
			})
		})
	}
	window.__tx_shape.processors.validityMessages = processNode
	document.querySelectorAll('[data-yf-form]').forEach(form => processNode(form))
}