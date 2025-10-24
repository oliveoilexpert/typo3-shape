const connectElement = el => {
	const form = el.closest('[data-shape-form]') ?? el.querySelector('[data-shape-form]')
	if (!form) return
	
	// Set up event delegation on form using capture phase (only once)
	if (!form.__shapeValidationDelegation) {
		form.__shapeValidationDelegation = true
		
		// Use capture phase to catch non-bubbling 'invalid' events
		form.addEventListener('invalid', e => {
			if (!e.target.matches('[data-shape-control]')) return
			
			const control = e.target
			const error = control.closest('[data-shape-field]')?.querySelector('[data-shape-error]')
			if (!error) return
			
			e.preventDefault()
			form.querySelector('[data-shape-control]:invalid')?.focus()
			error.classList.remove('--hidden')
			error.innerHTML = `<div>${control.dataset.yfValidationMessage || control.validationMessage}</div>`
		}, true) // capture phase
		
		form.addEventListener('change', e => {
			if (!e.target.matches('[data-shape-control]')) return
			
			const control = e.target
			if (control.validity.valid) {
				const error = control.closest('[data-shape-field]')?.querySelector('[data-shape-error]')
				error?.classList.add('--hidden')
			}
		})
	}
}

// Listen for dynamic content
document.addEventListener('shape:connect', e => connectElement(e.detail.element))

// Process initial content
document.querySelectorAll('[data-shape-form]').forEach(form => connectElement(form))
