import('https://cdn.jsdelivr.net/npm/jexl@2.3.0/+esm').then((jexl) => {
	if (!window.__tx_shape) {
		window.__tx_shape = {}
	}
	window.__tx_shape.jexl = jexl.default
	import('./conditional-fields.js')
});
