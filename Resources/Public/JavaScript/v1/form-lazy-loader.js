{
	const loader = document.getElementById('shape-form-lazy-loader')
	fetch(loader.dataset.fetch).then(r => r.text()).then(html => {
		loader.insertAdjacentHTML('beforebegin', html)
		window.requestAnimationFrame(() => {
			window.__t3_tx_shape.process(loader.previousElementSibling)
			loader.remove()
		})
	})
}