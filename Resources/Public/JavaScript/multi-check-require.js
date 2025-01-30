{
	// todo: doesnt work properly when one box is already checked, also only works if theres only one group, completely broken
	const processNode = el => {
		const boxes = el.querySelectorAll('[data-shape-multi-check-required="1"]')
		boxes.forEach(((box, i) => {
			if (i) box.removeAttribute('required')
			const group = box.parentElement
			const first = group.querySelector('[data-shape-multi-check-required]')
			box.addEventListener('change', () => {
				if (!box.parentElement.querySelector('input:checked')) {
					first.setAttribute('required', 'required');
				} else {
					first.removeAttribute('required');
				}
			});
		}))
	}
	window.__tx_shape.processors.multiCheckRequire = processNode
	document.querySelectorAll('[data-shape-form]').forEach(form => processNode(form))
}