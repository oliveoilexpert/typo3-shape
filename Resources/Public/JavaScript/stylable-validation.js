{
	const processNode = el => {
		const form = el.closest('[data-yf-form]') ?? el.querySelector('[data-yf-form]')
		el.querySelectorAll('[data-yf-control]').forEach(control => {
			const error = control.closest('[data-yf-field]').querySelector(`[data-yf-error]`)
			if (!error) return
			control.addEventListener('invalid', e => {
				e.preventDefault()
				form.querySelector('[data-yf-control]:invalid').focus()
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
	document.querySelectorAll('[data-yf-form]').forEach(form => processNode(form))
}