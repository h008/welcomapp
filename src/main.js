import { generateFilePath } from '@nextcloud/router'

import Vue from 'vue'
import App from './App'

// eslint-disable-next-line
 __webpack_public_path__ = generateFilePath(appName, '', 'js/')

Vue.mixin({ methods: { t, n } })
Vue.prototype.t = window.t
Vue.prototype.n = window.n
Vue.prototype.OC = window.OC
Vue.prototype.OCA = window.OCA
export default new Vue({
	el: '#content',
	render: h => h(App),
})
