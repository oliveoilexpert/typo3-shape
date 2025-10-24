{
	const processNode = el => {
		const form = el.closest('[data-shape-form]') ?? el.querySelector('[data-shape-form]')
		el.querySelectorAll('[data-shape-control]').forEach(control => {
			const error = control.closest('[data-shape-field]').querySelector(`[data-shape-error]`)
			if (!error) return
			control.addEventListener('invalid', e => {
				e.preventDefault()
				form.querySelector('[data-shape-control]:invalid').focus()
				error.classList.remove('--hidden')
				error.innerHTML = `<div>${control.dataset.yfValidationMessage || control.validationMessage}</div>`
			})
			control.addEventListener('change', () => {
				if (control.validity.valid) {
					error.classList.add('--hidden')
				}
			})
		})
	}
	window.__t3_tx_shape.processors.stylableValidation = processNode
	document.querySelectorAll('[data-shape-form]').forEach(form => processNode(form))
}