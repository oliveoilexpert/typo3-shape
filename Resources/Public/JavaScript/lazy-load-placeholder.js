{
	const ph = document.getElementById('shape-lazy-load-placeholder')
	fetch(ph.dataset.fetch).then(r => r.text()).then(html => {
		ph.insertAdjacentHTML('beforebegin', html)
		window.requestAnimationFrame(() => {
			window.__t3_tx_shape.process(ph.previousElementSibling)
			ph.remove()
		})
	})
}