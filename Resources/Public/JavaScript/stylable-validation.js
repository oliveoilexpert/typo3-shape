{
	const processNode = el => {
		const form = el.closest('[data-shape-form]') ?? el.querySelector('[data-shape-form]')
		el.querySelectorAll('[data-shape-control]').forEach(control => {
			const error = control.closest('[data-shape-field]').querySelector(`[data-shape-error]`)
			if (!error) return
			control.addEventListener('invalid', e => {
				form.querySelector('[data-shape-control]:invalid').focus()
				e.preventDefault()
				let message = control.dataset.shapeValidityMessage || control.validationMessage
				error.classList.add('-visible')
				error.innerHTML = `<div>${message}</div>`
			})
			control.addEventListener('change', () => {
				if (control.validity.valid) {
					error.classList.remove('-visible')
				}
			})
		})
	}
	window.__tx_shape.processors.stylableValidation = processNode
	document.querySelectorAll('[data-shape-form]').forEach(form => processNode(form))
}