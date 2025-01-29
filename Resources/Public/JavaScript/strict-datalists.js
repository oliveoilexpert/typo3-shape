{
	const processNode = el => {
		el.querySelectorAll('[data-shape-strict-list="1"]').forEach(list => {
			const input = document.querySelector(`[list="${list.id}"]`)
			input?.addEventListener('change', () => {
				const option = list.querySelector(`option[value="${input.value}"]`)
				if (option || (!input.required && !input.value)) {
					input.setCustomValidity('')
				} else {
					input.setCustomValidity(input.dataset.shapeValidityMessage || 'Value must be one of the options in the list')
				}
			})
		})
	}

	if (!window.__tx_shape) {
		window.__tx_shape = {
			processors: {},
		}
	}

	window.__tx_shape.processors.strictDatalists = processNode
	document.querySelectorAll('[data-shape-form]').forEach(form => processNode(form))
}