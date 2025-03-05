if (!window.__t3_tx_shape) window.__t3_tx_shape = {
	processors: {},
	process: el => Object.entries(window.__t3_tx_shape.processors).forEach(([key, proc]) => proc(el))
}