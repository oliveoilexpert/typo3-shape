{
	const connectElement = el => {
		const form = el.closest('[data-shape-form]') ?? el.querySelector('[data-shape-form]')
		if (!form) return

		if (form.__shapeFocusPassSetup) return
		const template = form.querySelector('[data-shape-focus-pass-template]')
		if (!template) return

		form.__shapeFocusPassSetup = true

		setTimeout(() => {
			form.addEventListener('focusin', () => {
				const frag = template.content.cloneNode(true)
				const field = frag.querySelector('[data-focus-pass]')
				field.value = field.dataset.focusPass
				template.parentElement.appendChild(frag)
			}, { once: true })
		}, 200)
	}

	document.addEventListener('shape:connect', e => connectElement(e.detail.element))
	document.querySelectorAll('[data-shape-form]').forEach(form => connectElement(form))
}