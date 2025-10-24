{
	const loader = document.getElementById('shape-form-lazy-loader')
	if (loader?.dataset?.fetch) {
		fetch(loader.dataset.fetch)
			.then(r => r.text())
			.then(html => {
				loader.insertAdjacentHTML('beforebegin', html)
				requestAnimationFrame(() => {
					const element = loader.previousElementSibling

					document.dispatchEvent(new CustomEvent('shape:connect', {
						detail: { element }
					}))

					loader.remove()
				})
			})
	}
}