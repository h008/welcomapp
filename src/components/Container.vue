<template>
	<div class="container">
		<Header :header-config="headerConfig" />
		<div class="card">
			<Detail v-if="mode=='detail'"
				:note="localCurrentNote"
				:user="user"
				:categories="categories"
				:tags="tags" />
			<div v-else>
				<h2>カテゴリー:{{ title }}</h2>
				<PinList
					:filter="pinFilter"
					:user="user"
					:categories="categories"
					:tags="tags"
					:mode.sync="localMode"
					:current-note.sync="localCurrentNote"
					:dialog.sync="dialog" />
				<List
					:filter.sync="localFilter"
					:user="user"
					:mode.sync="localMode"
					:notes.sync="localNotes"
					:current-note.sync="localCurrentNote"
					:dialog.sync="dialog"
					:tags="tags" />
			</div>
			<Form :note.sync="localCurrentNote"
				:dialog.sync="dialog"
				:categories="categories"
				:tags="tags"
				:user="user" />
		</div>
	</div>
</template>
<script>
import List from './List.vue'
import PinList from './PinList.vue'
import Detail from './Detail.vue'
import Form from './Form.vue'
import Header from './Header.vue'
import { emit } from '@nextcloud/event-bus'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import Mymodules from '../js/modules'
export default {
	name: 'Container',
	components: {
		List,
		PinList,
		Detail,
		Form,
		Header,
	},
	props: {
		notes: {
			type: Array,
			default: () => { return [] },
		},
		currentNote: {
			type: Object,
			default: () => { return {} },
		},
		categories: {
			type: Array,
			default: () => { return [] },
		},
		tags: {
			type: Array,
			default: () => { return [] },
		},
		user: {
			type: Object,
			default: () => { return {} },
		},
		users: {
			type: Array,
			default: () => { return [] },
		},
		mode: {
			type: String,
			default: 'list',
		},
		filter: {
			type: Object,
			default: () => { return { category: 0 } },
		},
		headerConfig: {
			type: Object,
			default: () => { return {} },
		},

	},

	data() {
		return {
			dialog: false,
			userDir: '',

		}
	},
	computed: {
		localCurrentNote: {
			get() {
				return this.currentNote

			},
			set(val) {
				this.$emit('update:currentNote', val)
			},

		},
		localNotes: {
			get() {
				return this.notes
			},
			set(val) {
				this.$emit('update:notes', val)

			},
		},
		localMode: {
			get() {
				return this.mode
			},
			set(val) {
				this.$emit('update:mode', val)
			},
		},
		pinFilter() {
			const tmpFilter = Object.assign({}, this.localFilter)
			return Object.assign(tmpFilter, { pinFlag: true })
		},
		localFilter: {
			get() {
				return this.filter
			},
			set(val) {
				this.$emit('update:filter', val)
			},

		},
		title() {
			let categoryName = ''
			if (this.filter.category > 0 && this.categories && this.categories.length) {
				categoryName = this.categories.find((item) => Number(item.id) === Number(this.filter.category))?.category_name

			}
			if (!categoryName) {
				categoryName = 'すべて'
			}
			return categoryName
		},

	},
	watch: {
		currentNote(value, oldvalue) {
			if (value.id !== oldvalue.id) {

				if (value.userId && value.uuid) {
					this.fetchAutherInfo(value.userId)
					this.fetchShareInfo(value.shareInfo, this.user).then((userDir) => {
			 this.userDir = userDir

						// this.fetchDirInfo()
						if (value.id !== -1) {

							const fileInfo = this.fetchFileInfo()
							const dirInfo = this.fetchDirInfo()
							Promise.all([fileInfo, dirInfo]).then((fileListArray) => {
								this.compareFileInfo(fileListArray)
							})
						}
					})

				}

				if (this.currentNote.id === -1) {
					const path = `${this.user.id}/.announce_${value.uuid}`
					Mymodules.fetchDirInfoOrCreate(path)
					this.dialog = true
				} else {

					const item = this.notes.find((note) => note.id === value.id)
					if (!item || (item.id && item !== value)) {
						const tmpArray = this.notes.filter((note) => note.id > 0 && note.id !== value.id)
						if (value.id > 0) {
							tmpArray.unshift(value)

						}
						this.$emit('update:notes', tmpArray)

					}
				}
			}
		},
		dialog(val) {
			if (val) {
				emit('toggle-navigation', { open: false })
				return
			}
			if (!val && this.currentNote.id === -1 && this.currentNote.uuid) {
				const path = `${this.user.id}/.announce_${this.currentNote.uuid}`
				axios.delete(`/remote.php/dav/files/${path}`)
			}
			if (!this.currentNote || !this.currentNote.id || this.currentNote.id < 1) {
				const tmpArray = this.notes.filter((note) => note.id > 0)
				this.$emit('update:notes', tmpArray)
				this.localCurrentNote = {}

			}
		},
	},
	methods: {
		fetchAutherInfo(id) {

			axios.get(generateUrl(`/apps/welcomapp/getuser/${id}`)).then((userInfo) => {

				const user = userInfo.data
				this.$set(this.localCurrentNote, 'userInfo', user)
			})
		},

		async fetchDirInfo() {
			if (!this.user.id || !this.userDir || !this.localCurrentNote.uuid) {
				return Promise.resolve([])

			} else {

				const path = `${this.user.id}${this.userDir}`

				// const path = `${this.localCurrentNote.userId}/announce/${this.localCurrentNote.uuid}`
				return Mymodules.fetchDirInfo(path).then((result) => {
					 return result
				}
				)
			}
		},
		async fetchFileInfo() {
			if (this.localCurrentNote.uuid) {
				return axios.get(generateUrl(`/apps/welcomapp/getfiles/${this.localCurrentNote.uuid}`)).then((result) => {
					this.localCurrentNote.fileTarget = this.userDir
					if(!result ||!result.data||!result.data.length){
						return []
					}
					return result.data.map((elem) => {
						elem.fileTarget = this.userDir
						if (elem.filetype === 'folder') {
							elem.userRef = generateUrl(`/f/${elem.id}`)

						} else {
							// TODO
							elem.userRef = `/remote.php/dav/files/${this.user.id}${this.userDir}/${elem.filename}`
						}
						// axios.get(elem.userRef).then((result) => {
						// elem.refcheck = result.data
						// })
						return elem
					})

				})

			} else {

				return Promise.resolve([])
			}

		},
		compareFileInfo(fileListArray) {
			const tmpNote = this.localCurrentNote
			let realFiles = []
			let dbFiles = []
			let fileInfo = []
			let dirInfo = []
			if (fileListArray && fileListArray.length) {
				if (fileListArray[1] && fileListArray[1].length) {
			 realFiles = fileListArray[1].map((rfile) => Number(rfile.fileId))

				}
				if (fileListArray[0] && fileListArray[0].length) {
			 dbFiles = fileListArray[0].map((dfile) => Number(dfile.id))
			 fileInfo = fileListArray[0].map((dFileInfo) => {
						dFileInfo.exist = realFiles.includes(Number(dFileInfo.id))
						return dFileInfo
					})

				}
				if (dbFiles && dbFiles.length) {
			 dirInfo = fileListArray[1].map((rFileInfo) => {
						rFileInfo.registered = dbFiles.includes(Number(rFileInfo.fileId))
						const target = dbFiles.find((file) => Number(file.id === Number(rFileInfo.fileId)))
						if (target) {
							rFileInfo.isEyecatch = target.isEyecatch

						}

						return rFileInfo
					})

				}

			}
			tmpNote.fileInfo = fileInfo
			tmpNote.dirInfo = dirInfo
			// this.$set(this.localCurrentNote, 'fileInfo', fileInfo)
			// this.$set(this.localCurrentNote, 'dirInfo', dirInfo)

			 this.localCurrentNote = Object.assign({}, tmpNote)

			// this.$emit('update:currentNote', Object.assign({}, tmpNote))

		},
		fetchShareInfo(shareInfoStr, user) {
			console.info('here')
			if (shareInfoStr) {
				const shareInfo = JSON.parse(shareInfoStr)
				if (!shareInfo.length) {
					console.info(shareInfo)

				}
				if (!user.groups?.length) { return '' }
				const shareId = shareInfo.filter((info) => user.groups.includes(info.gid)).map((elm) => elm.shareId)[0]

			 return axios.get(`/ocs/v2.php/apps/files_sharing/api/v1/shares/${shareId}`, { headers: { 'OCS-APIRequest': true } }).then((result) => {

			 return result?.data?.ocs?.data[0]?.file_target
			 })
			 }

		},
	},

}
</script>
<style>
.container{
	padding: 20px;
	padding-top: 30px;
}

.card{
	padding: 20px 40px;
	border: lightslategrey;
	border-style: solid;
	border-width: 1px;
	border-radius: 10px;
}
</style>
