if (!window.__tx_shape) window.__tx_shape = {
	processors: {},
	process: el => Object.entries(window.__tx_shape.processors).forEach(([key, proc]) => proc(el))
}