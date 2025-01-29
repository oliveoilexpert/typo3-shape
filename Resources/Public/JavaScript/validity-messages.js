{
	const processNode = el => {
		el.querySelectorAll('[data-shape-validity-message]').forEach(input => {
			input.addEventListener('invalid', () => {
				input.setCustomValidity(input.dataset.shapeValidityMessage)
			})
			input.addEventListener('change', () => {
				input.setCustomValidity('')
			})
		})
	}

	if (!window.__tx_shape) {
		window.__tx_shape = {
			processors: {},
		}
	}

	window.__tx_shape.processors.validityMessages = processNode
	document.querySelectorAll('[data-shape-form]').forEach(form => processNode(form))
}