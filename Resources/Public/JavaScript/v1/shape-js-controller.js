import jstin from '../lib/subscript-9.0.0/justin.min.js'

class ShapeJsController extends HTMLElement {
  connectedCallback() {
    this.form = this.closest('form[data-shape-form]')
    if (!this.form) return

    this.formName = this.form.dataset.shapeForm

    // Process initial content
    this.processNode(this.form)

    // Watch for dynamically added content
    this.observer = new MutationObserver(mutations => {
      mutations.forEach(mutation => {
        mutation.addedNodes.forEach(node => {
          if (node.nodeType === Node.ELEMENT_NODE) {
            this.processNode(node)
          }
        })
      })
    })

    this.observer.observe(this.form, {
      childList: true,
      subtree: true
    })
  }

  disconnectedCallback() {
    this.observer?.disconnect()
  }

  processNode(el) {
    this.setupConditionalFields(el)
    this.setupRepeatableContainers(el)
    this.setupStylableValidation(el)
    this.setupValidationMessages(el)
    this.setupLazyLoader(el)
  }

  // Conditional Fields
  setupConditionalFields(el) {
    const controls = el.querySelectorAll('[data-shape-control]')
    controls.forEach(control => {
      if (!control.__shapeConditionalListener) {
        control.__shapeConditionalListener = true
        control.addEventListener('change', () => this.evaluateConditions())
      }
    })

    if (controls.length > 0) {
      requestAnimationFrame(() => this.evaluateConditions())
    }
  }

  evaluateConditions() {
    const fields = this.form.querySelectorAll('[data-shape-condition]')
    if (!fields.length) return

    const data = Object.fromEntries(new FormData(this.form))

    fields.forEach(field => {
      const cond = field.dataset.shapeCondition
      if (!cond) return

      const inputs = field.querySelectorAll('[data-shape-control]')
      const isVisible = jstin(cond)({
        value: fId => data[`tx_shape_form[${this.formName}][${fId}]`] ?? null,
        formData: str => data[`tx_shape_form[${this.formName}]${str}`] ?? null
      })

      field.classList.toggle('--hidden', !isVisible)
      inputs.forEach(inp => inp.disabled = !isVisible)
    })
  }

  // Repeatable Containers
  setupRepeatableContainers(el) {
    // Fix condition placeholders in existing repeatable items
    el.querySelectorAll('[data-shape-repeatable-item]').forEach(item => {
      item.querySelectorAll('[data-shape-condition*="[__INDEX]"]').forEach(field => {
        field.setAttribute(
          'data-shape-condition',
          field.dataset.shapeCondition.replaceAll('[__INDEX]', `[${item.dataset.shapeRepeatableItem}]`)
        )
      })
    })

    // Setup add buttons
    el.querySelectorAll('[data-shape-repeatable-add]').forEach(btn => {
      if (!btn.__shapeAddListener) {
        btn.__shapeAddListener = true
        btn.addEventListener('click', e => this.handleRepeatableAdd(e))
      }
    })

    // Setup remove buttons
    el.querySelectorAll('[data-shape-repeatable-remove]').forEach(btn => {
      if (!btn.__shapeRemoveListener) {
        btn.__shapeRemoveListener = true
        btn.addEventListener('click', e => this.handleRepeatableRemove(e))
      }
    })
  }

  handleRepeatableAdd(e) {
    const btn = e.currentTarget

    // Debounce
    if (!btn.disabled) {
      btn.disabled = true
      setTimeout(() => btn.disabled = false, 500)
    }

    const container = document.getElementById(btn.dataset.shapeRepeatableAdd)
    if (!container) return

    const tmpl = container.querySelector('template')
    if (!tmpl) return

    const i = tmpl.dataset.iteration
    const clone = tmpl.content.cloneNode(true)

    // Update condition placeholders
    clone.querySelectorAll('[data-shape-condition]').forEach(wrap => {
      const cond = wrap.dataset.shapeCondition
      if (cond) {
        wrap.setAttribute('data-shape-condition', cond.replaceAll('[__INDEX]', `[${i}]`))
      }
    })

    // Update form input names and IDs
    clone.querySelectorAll('input, textarea, select').forEach(input => {
      input.name = input.name.replaceAll('[__INDEX]', `[${i}]`)
      const newId = input.id.replaceAll('[__INDEX]', `[${i}]`)
      clone.querySelector(`label[for="${input.id}"]`)?.setAttribute('for', newId)
      input.id = newId
    })

    // Set repeatable item index
    const repeatableItem = clone.querySelector('[data-shape-repeatable-item]')
    if (repeatableItem) {
      repeatableItem.dataset.shapeRepeatableItem = i
    }

    // Append and process new nodes
    const nodes = [...clone.childNodes]
    container.appendChild(clone)

    nodes.forEach(node => {
      if (node.nodeType === Node.ELEMENT_NODE) {
        this.processNode(node)
      }
    })

    tmpl.dataset.iteration = (parseInt(i) + 1).toString()
  }

  handleRepeatableRemove(e) {
    e.currentTarget.closest('[data-shape-repeatable-item]')?.remove()
  }

  // Stylable Validation
  setupStylableValidation(el) {
    el.querySelectorAll('[data-shape-control]').forEach(control => {
      if (control.__shapeValidationListener) return
      control.__shapeValidationListener = true

      const error = control.closest('[data-shape-field]')?.querySelector('[data-shape-error]')
      if (!error) return

      control.addEventListener('invalid', e => {
        e.preventDefault()
        this.form.querySelector('[data-shape-control]:invalid')?.focus()
        error.classList.remove('--hidden')
        error.innerHTML = `<div>${control.dataset.shapeValidationMessage || control.validationMessage}</div>`
      })

      control.addEventListener('change', () => {
        if (control.validity.valid) {
          error.classList.add('--hidden')
        }
      })
    })
  }

  // Validation Messages
  setupValidationMessages(el) {
    el.querySelectorAll('[data-shape-validation-message]').forEach(input => {
      if (input.__shapeValidationMsgListener) return
      input.__shapeValidationMsgListener = true

      input.addEventListener('invalid', () => {
        input.setCustomValidity(input.dataset.shapeValidationMessage)
      })

      input.addEventListener('change', () => {
        input.setCustomValidity('')
      })
    })
  }

  // Lazy Loader
  setupLazyLoader(el) {
    const loader = el.matches?.('#shape-form-lazy-loader')
      ? el
      : el.querySelector?.('#shape-form-lazy-loader')

    if (!loader?.dataset?.fetch) return

    fetch(loader.dataset.fetch)
      .then(r => r.text())
      .then(html => {
        loader.insertAdjacentHTML('beforebegin', html)
        requestAnimationFrame(() => {
          this.processNode(loader.previousElementSibling)
          loader.remove()
        })
      })
  }
}

customElements.define('shape-js-controller', ShapeJsController)
