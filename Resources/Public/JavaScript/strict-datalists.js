{
	const processNode = el => {
		el.querySelectorAll('[data-shape-strict-list="1"]').forEach(list => {
			const input = document.querySelector(`[list="${list.id}"]`)
			input?.addEventListener('change', () => {
				const opt = list.querySelector(`option[value="${input.value}"]`)
				if (opt || (!input.required && !input.value)) {
					input.setCustomValidity('')
				} else {
					input.setCustomValidity(input.dataset.shapeValidityMessage || 'Value must be one of the options in the list')
				}
			})
		})
	}
	window.__tx_shape.processors.strictDatalists = processNode
	document.querySelectorAll('[data-shape-form]').forEach(form => processNode(form))
}