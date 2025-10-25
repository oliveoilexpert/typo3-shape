{
	const connectElement = el => {
		const form = el.closest('[data-shape-form]') ?? el.querySelector('[data-shape-form]')
		if (!form) return

		if (!form.__shapeValidationMsgDelegation) {
			form.__shapeValidationMsgDelegation = true

			form.addEventListener('invalid', e => {
				if (!e.target.matches('[data-shape-validation-message]')) return
				e.target.setCustomValidity(e.target.dataset.shapeValidationMessage)
			}, true)

			form.addEventListener('change', e => {
				if (!e.target.matches('[data-shape-validation-message]')) return
				e.target.setCustomValidity('')
			})
		}
	}

	document.addEventListener('shape:connect', e => connectElement(e.detail.element))
	document.querySelectorAll('[data-shape-form]').forEach(form => connectElement(form))
}