import 'bootstrap'

import axios from 'axios'
window.axios = axios

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// Set CSRF token from <meta name="csrf-token"> for all requests
const csrfToken = document.querySelector('meta[name="csrf-token"]')
if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content')
}

window.axios.defaults.withCredentials = true
window.axios.defaults.withXSRFToken = true
