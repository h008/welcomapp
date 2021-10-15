<template>
	<div id="mordalform" :class="modalclass" @click.stop="clicked">
		<a href="#!" class="modal__overlay" />

		<div v-if="note && dialog"
			class="modal__window"
			:class="contentWidth"
			@click.stop="donothing">
			<div class="modal__action">
				<div class="modal__button">
					<Actions>
						<ActionButton :icon="iconScreenWidth" @click="toggleScreenWidth" />
					</Actions>
				</div>
			</div>
			<div class="modal__content">
				<div>
					<div class="input__label">
						件名
					</div>

					<input
						ref="title"
						v-model="localNote.title"
						class="titleinput"
						type="text">
					<div class="input__label">
						カテゴリ
					</div>
					<div>
						<Multiselect
							v-model="selectedCategory"
							:options="categories"
							track-by="id"
							label="category_name"
							class="multiselect" />
					</div>
					<div class="input__label">
						内容
					</div>
					<div>
						<Editor
							id="tm"
							v-model="editorContent"
							:other_options="options" />
					</div>
					<div class="input__label">
						ファイル
					</div>
					<div>
						<div class="fixed__row">
							<input
								id="fileinput"
								ref="fileinput"
								class="titleinput"
								type="file"
								@change="fileselect">
							<Actions>
								<ActionButton icon="icon-upload" @click="uploadFile">
									アップロード
								</ActionButton>
							</Actions>
						</div>
						<DirList :items.sync="dirInfo"
							:user-id="user.id"
							:file-info.sync="localNote.fileInfo"
							@addcontent="addContent" />
						<Thums :files="eyecatchFiles" />
						<div class="input__label">
							タグ
						</div>
						<div class="fixed__row">
							<Multiselect
								v-model="selectedTags"
								:multiple="true"
								:options="tags"
								track-by="id"
								:searchable="true"
								:taggable="true"
								:hide-selected="true"
								label="tag_name">
								<template slot="tag" slot-scope="{ option, remove }">
									<span
										class="multiselect__tag"
										:style="{ 'background-color': option.color }">
										<span> {{ option.tag_name }}</span>
										<span
											class="custom__remove icon-close"
											@click="remove(option)" />
									</span>
								</template>
							</Multiselect>
						</div>
						<div class="input__label">
							グループを選択
						</div>
						<div class="fixed__row">
							<Multiselect
								v-model="selectedGroups"
								:multiple="true"
								:options="user.gdata"
								track-by="id"
								:searchable="true"
								label="name" />
						</div>
						<div class="fixed__row">
							<div class="row__element">
								<input
									id="pin_flag"
									v-model="pinFlag"
									:disabled="!canSave"
									type="checkbox">
								<label for="pin_flag">常に上部に表示する</label>
							</div>
							<div class="row__element">
								<input
									id="pub_flag"
									v-model="pubFlag"
									:disabled="!selectedGroups.length"
									type="checkbox">
								<label for="pin_flag">公開する</label>
							</div>
						</div>
					</div>

					<div class="fixed__row">
						<div class="row__element_end">
							<input
								type="button"
								:value="t('welcomapp', '閉じる')"
								@click="closeDialog">
							<input
								type="button"
								class="primary fixed__row__end"
								:value="t('welcomapp', '保存')"
								:disabled="!canSave"
								@click="saveNote">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
import Editor from 'vue-tinymce-editor'
import '../../js/tinymce_languages/langs/ja.js'
import Mymodules from '../js/modules'
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'
import Actions from '@nextcloud/vue/dist/Components/Actions'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import DirList from './DirList.vue'
import Thums from './Thums.vue'
import dayjs from 'dayjs'
dayjs.extend(require('dayjs/plugin/utc'))
dayjs.extend(require('dayjs/plugin/timezone'))
dayjs.tz.setDefault('Asia/Tokyo')
// const dav = require('dav')
export default {
	name: 'Form',
	components: {
		Editor,
		Multiselect,
		Actions,
		ActionButton,
		DirList,
		Thums,
	},
	props: {
		note: {
			type: Object,
			default: () => {
				return { id: -1, title: '', content: '', category: 0 }
			},
		},
		dialog: {
			type: Boolean,
			default: false,
		},
		categories: {
			type: Array,
			default: () => {
				return []
			},
		},
		tags: {
			type: Array,
			default: () => {
				return []
			},
		},
		user: {
			type: Object,
			default: () => { return {} },
		},
	},
	data() {
		return {
			localNote: {},
			titleStr: '',
			options: {
				language: 'ja',
				height: 400,
			},
			selectedCategory: {},
			selectedTags: [],
			selectedGroups: [],
			fileDir: '',
			selectedFile: null,
			dirInfo: [],
			contentWidth: 'w800',
			iconScreenWidth: 'icon-fullscreen',
			// editorContent: 'test',
		}
	},
	computed: {
		modalclass() {
			if (this.dialog) {
				return 'modal__wrapper modal__wrapper_visible'
			} else {
				return 'modal__wrapper modal__wrapper_hidden'
			}
		},
		eyecatchFiles() {
			return this.dirInfo.filter((file) => file.isEyecatch === '1')
		},
		editorContent: {
			get() {
				return this.localNote.content || ''
			},
			set(val) {
				this.$set(this.localNote, 'content', val)
			},
		},
		canSave() {
			return (this.localNote.title && this.selectedCategory.id && this.localNote.content)
		},
		pubFlag: {
			get() { return Number(this.localNote.pubFlag) === 1 },
			set(val) {
				if (val) {
					this.localNote.pubFlag = 1
			    } else {
					this.localNote.pubFlag = 0
				}
			},
		},
		pinFlag: {
			get() { return Number(this.localNote.pinFlag) === 1 },
			set(val) {
				if (val) {
					this.localNote.pinFlag = 1
			    } else {
					this.localNote.pinFlag = 0
				}
			},
		},
	},
	watch: {
		note(val) {
			this.initializeNote(val)
		},
		selectedCategory(val) {
			if (val && val.id) {
				this.localNote.category = val.id
			}
		},
		selectedTags(val) {
			const mappedTag = val.map((elm) => {
				return elm.id
			})
			this.localNote.tags = mappedTag.join(',')
			// console.info(mappedTag)
		},
		selectedGroups(val) {
			if (!val || !val.length) {
				this.localNote.pubFlag = 0
			}

		},
		dialog(val) {
			if (!val) {
				this.selectedCategory = {}
				this.selectedTags = []
				this.dirInfo = []
				this.$emit('update:note', {})
			} else {
				this.initializeNote(this.note)
			}
		},
	},
	mounted() {},

	methods: {
		clicked() {
			this.closeDialog()
		},
		toggleScreenWidth() {
			// console.info('full')
			if (this.contentWidth === 'w800') {
				this.contentWidth = 'full'
				this.iconScreenWidth = 'icon-history'
			} else {
				this.contentWidth = 'w800'
				this.iconScreenWidth = 'icon-fullscreen'

			}
		},
		addContent(e) {

			this.$set(this.localNote, 'content', `${this.localNote.content}${e}`)

		},
		donothing() {},
		initializeNote(val) {

			this.localNote = Object.assign({}, val)
			// if (val.pubFlag !== 1) { this.localNote.pubFlag = false }
			// if (val.pinFlag !== 1) { this.localNote.pinFlag = false }

			if (val.category && this.categories.length) {
				this.selectedCategory = this.categories.find(
					(category) => Number(category.id) === Number(val.category)
				)
			} else {
				this.selectedCategory = {}
			}
			if (val.tags && this.tags.length) {
				const tmpArray = val.tags.split(',')

				this.selectedTags = tmpArray.map((elm) => {
					return this.tags.find((tag) => Number(tag.id) === Number(elm))
				})
			} else {
				this.selectedTags = []
			}
			if (val.shareInfo) {
				const shareInfo = JSON.parse(val.shareInfo)
				const selectedGroups = shareInfo.map((el) => {
					return this.user.gdata.find((group) => group.id === el.gid)
				})
				this.selectedGroups = selectedGroups
			}
			if (val.uuid && this.dialog) {
				this.setDirInfo(`${this.user.id}/.announce_${val.uuid}`)
			} else { this.dirInfo = [] }
			if (val.content) {
				const tmIfr = document.getElementById('tm_ifr')
		 if (tmIfr && tmIfr.contentWindow) {
					tmIfr.contentWindow.document.getElementById('tinymce').innerHTML = val.content || ''

		 }

			}
		},

		saveNote() {
			// console.info(this.localNote)
			this.shareFolder().then((shareInfo) => {
				this.localNote.shareInfo = shareInfo
				Mymodules.saveNote(this.localNote)
					.then((result) => {

						if (result.data && result.data.id) {
							this.localNote.id = result.data.id
							this.localNote = result.data
							this.saveFilesInfo()
							this.shareFolder()
							this.$emit('update:note', this.localNote)
							this.$emit('update:dialog', false)
						}
					})
					.catch((e) => {
						console.error(e)
					})
			})
		},
		saveFilesInfo() {
			if (this.dirInfo.length) {
				this.dirInfo.forEach((item) => {
					if (item.filename === `.announce_${this.localNote.uuid}`) {
						console.info('parentFolder')

					} else {
						console.info(item.filename)

						const updated = dayjs.tz(item.modified, 'Asia/Tokyo').format('YYYY-MM-DD HH:mm:ss')
						if (!item.isEyecatch || item.isEyecatch === 'false' || Number(item.isEyecatch) === 0) { item.isEyecatch = 0 } else { item.isEyecatch = 1 }
						if (!item.hasPreview || item.hasPreview === 'false' || Number(item.hasPreview) === 0) { item.hasPreview = 0 } else { item.hasPreview = 1 }
						const data = {
							id: item.fileId,
							announceId: this.localNote.id,
							filename: item.filename,
							fileurl: this.localNote.uuid,
							filetype: item.filetype || 'folder',
							isEyecatch: item.isEyecatch,
							href: item.href2,
							hasPreview: item.hasPreview,
							updated,
							size: item.size,
							shareId: this.user.shareId,

						}
					 console.info(data)

						axios.post(generateUrl('/apps/welcomapp/files'), data).then((result) => {
						 console.info('saveFile')
						 console.info(result)
						}).catch((e) => {
						 console.info('saveFileError')
							console.error(e)
						})
					}

				}
				)

			}
		},
		getShareInfo(path) {
			 return axios.get('/ocs/v2.php/apps/files_sharing/api/v1/shares', { params: { path, reshares: true, subfiles: false } }).then((result) => {
				const shareInfo = result?.data?.ocs?.data
				return shareInfo
			})

		},
		shareFolder() {
			if (!this.user.id || !this.localNote.uuid) {
				return
			}
			const path = `/.announce_${this.localNote.uuid}`
			const sharedGids = Mymodules.fetchDirInfoOrCreate(`${this.user.id}${path}`).then(() => {

				return this.getShareInfo(path).then((shareInfo) => {
					return shareInfo.filter((el) => el.share_type === 1).map((share) => { return { gid: share.share_with, shareId: share.id } })
				})

			})

			if (!this.selectedGroups || !this.selectedGroups.length) {

				// 現在のシェアを削除
				// return
			}

			return sharedGids.then((shares) => {
				const sharedGids = shares.map((share) => share.gid)
				const selectedGids = this.selectedGroups.map((selected) => selected.id)
				const unshares = shares.filter((share) => !selectedGids.includes(share.gid))

				const promiseArray = this.selectedGroups.map((group) => {
				// すでに共有されているIDリストgidにgroup.idが含まれていなければ新たに共有を作る
					if (!sharedGids.includes(group.id)) {
						const data = { path, shareType: 1, shareWith: group.id, publicUpload: 'false', permissions: 1 }
						return axios.post('/ocs/v2.php/apps/files_sharing/api/v1/shares', data, { headers: { 'OCS-APIRequest': true } }).then((result) => {
							const shareId = result?.data?.ocs?.data?.id
							return { gid: group.id, shareId }
						}).catch((e) => {
							// console.info('postErr')
							// console.info(e)
							return { gid: '', shareId: '' }
						})
					} else {
						return shares.find((share) => share.gid === group.id)
					}
				})
				const promiseArray2 = unshares.map((unshare) => {
					if (!unshare.shareId) { return {} }
					return axios.delete(`/ocs/v2.php/apps/files_sharing/api/v1/shares/${unshare.shareId}`).catch((e) => {
						// console.info(e)
						 })
				})
				Promise.all(promiseArray2).then((result) => {
					// console.info('unshareResult')
					// console.info(result)

				})

				return Promise.all(promiseArray).then((tmpArray) => {
					const tmpStr = JSON.stringify(tmpArray)
					// console.info('tmpStr')
					// console.info(typeof tmpStr)
					return tmpStr

				})
			})

		},

		closeDialog() {
			this.$emit('update:dialog', false)
		},
		fileselect(e) {
			this.selectedFile = e.target?.files[0]
		},
		setDirInfo(path) {

			Mymodules.fetchDirInfo(path).then((result) => {
				if (result.length > 1) {

					let tmpArray = result.filter((info) => info.filename !== this.note.uuid)
					if (this.localNote.fileInfo && this.localNote.fileInfo.length) {
						tmpArray = tmpArray.map((file) => {
							const target = this.localNote.fileInfo.find((fileinfo) => Number(fileinfo.id) === Number(file.fileId))
							if (target) {
								file.isEyecatch = target.is_eyecatch

							}
							return file
						})
					}
					this.dirInfo = tmpArray

				}

			})
		},
		uploadFile() {

			if (this.selectedFile) {
				if (this.user.id && this.note.uuid) {
					const path = `${this.user.id}/.announce_${this.note.uuid}`
					Mymodules.fetchDirInfo(path).then((result) => {
						this.fileDir = path
						axios.put(`/remote.php/dav/files/${path}/${this.selectedFile.name}`, this.selectedFile).then((result) => {
							if (result.status === 201 || result.status === 204) {
								this.setDirInfo(path)
							} else {
								// console.info(result)

							}

						}).catch((e) => {
							alert('error')
							console.error(e)
						})
					}).catch((e) => {
						alert('error')
						console.error(e)
					})

				}
			}

		},

	},
}
</script>
<style scoped>
.input__label {
	text-align: start;
	font-weight: 800;
}

input[type='checkbox'] {
	transform: scale(1.2);
	margin: 10px 6px 0 0;
}

label {
	vertical-align: middle;
	height: 100%;
	padding-bottom: 21px;
}

.row__element {
	vertical-align: middle;
	margin-left: 12px;
}

.row__element_end {
	vertical-align: middle;
	padding-top: 10px;
	margin-left: auto;
}

.fixed__row {
	margin-top: 4px;
	display: flex;
	vertical-align: middle;
	text-align: left;
}

.titleinput {
	width: 100%;
}

.modal__wrapper {
	z-index: 999;
	position: fixed;
	top: 0;
	right: 0;
	bottom: 0;
	left: 0;
	padding: 60px 10px;
	text-align: center;
}

.modal__wrapper_hidden {
	opacity: 0;
	visibility: hidden;
	transition: opacity 0.3s, visibility 0.3s;
}

.modal__wrapper_visible {
	opacity: 1;
	visibility: visible;
	transition: opasity 0.4s, visibility 0.4s;
}

.modal__wrapper::after {
	display: inline-block;
	height: 100%;
	margin-left: -0.5em;
	vertical-align: middle;
	content: '';
}

.modal__wrapper .modal__window {
	box-sizing: border-box;
	display: inline-block;
	z-index: 2;
	position: relative;
	/* width: 875px; */
	padding: 30px 30px 15px;
	border-radius: 2px;
	background: #fff;
	box-shadow: 0 0 30px rgba(0, 0, 0, 0.6);
	vertical-align: middle;
}

.modal__wrapper .modal__window .modal__content {
	/* max-height: 80vh; */
	overflow-y: auto;
}

.modal__wrapper .w800{
	width:875px;
	max-height:80vh;
	overflow-y:auto;
}

.modal__wrapper .full{
	width:100vw;
	max-height:100%;
	overflow-y:auto;
}

.modal-overlay {
	z-index: 1;
	position: absolute;
	top: 0;
	right: 0;
	bottom: 0;
	left: 0;
	background: rgba(0, 0, 0, 0.8);
}

.modal__wrapper .modal-close {
	z-index: 2;
	position: absolute;
	top: 0;
	right: 0;
	width: 35px;
	color: #95979c !important;
	font-size: 20px;
	font-weight: 700;
	line-height: 35px;
	text-align: center;
	text-decoration: none;
	text-indent: 0;
}

.modal__wrapper .modal-close:hover {
	color: #2b2e38 !important;
}

.modal__content{
	/* width:90%; */
	padding:20px;
}

.multiselect {
	width: 100%;
}

.modal__windowaction{
	display:flex;
	align-items: flex-end;
}

.modal__action{
	position:sticky;
	top:0px;
	left:0px;
	width:100%;
	z-index:999;
}

.modal__button{
	position:absolute;
	top:0px;
	right:0px;
}
@font-face{
	font-family:'tinymce';
	src:url('https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.9.6/skins/lightgray/fonts/tinymce.eot');
	src:
		url('https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.9.6/skins/lightgray/fonts/tinymce.eot?#iefix') format('embedded-opentype'),
		url('https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.9.6/skins/lightgray/fonts/tinymce.woff') format('woff'),
		url('https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.9.6/skins/lightgray/fonts/tinymce.ttf') format('truetype'),
		url('https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.9.6/skins/lightgray/fonts/tinymce.svg#tinymce') format('svg');
	font-weight:normal;
	font-style:normal
}
@font-face{
	font-family:'tinymce-small';
	src:url('http://127.0.0.1/fonts/tinymce-small.eot');
	src:url('http://127.0.0.1/fonts/tinymce-small.eot?#iefix') format('embedded-opentype'),url('http://127.0.0.1/fonts/tinymce-small.woff') format('woff'),url('http://127.0.0.1/fonts/tinymce-small.ttf') format('truetype'),url('http://127.0.0.1/fonts/tinymce-small.svg#tinymce') format('svg');
	font-weight:normal;font-style:normal
}

</style>
