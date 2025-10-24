const connectElement = el => {
	const form = el.closest('[data-shape-form]') ?? el.querySelector('[data-shape-form]')
	if (!form) return
	
	// Set up event delegation on form using capture phase (only once)
	if (!form.__shapeValidationMsgDelegation) {
		form.__shapeValidationMsgDelegation = true
		
		// Use capture phase to catch non-bubbling 'invalid' events
		form.addEventListener('invalid', e => {
			if (!e.target.matches('[data-shape-validation-message]')) return
			e.target.setCustomValidity(e.target.dataset.yfValidationMessage)
		}, true) // capture phase
		
		form.addEventListener('change', e => {
			if (!e.target.matches('[data-shape-validation-message]')) return
			e.target.setCustomValidity('')
		})
	}
}

// Listen for dynamic content
document.addEventListener('shape:connect', e => connectElement(e.detail.element))

// Process initial content
document.querySelectorAll('[data-shape-form]').forEach(form => connectElement(form))
