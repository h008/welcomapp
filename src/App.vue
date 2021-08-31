<template>
	<div id="content" class="app-welcomapp">
		<AppNavigation>
			<AppNavigationItem
				v-if="!loading"
				title="新着順"
				:icon="menuIcon(0)"
				@click="changeCategory(0)" />
			<span v-if="!loading && categories">
				<AppNavigationItem
					v-for="category of categories"
					:key="`ct_${category.id}`"
					:title="category.category_name"
					:icon="menuIcon(category.id)"
					@click="changeCategory(category.id)" />
			</span>
			<AppNavigationItem
				v-if="!loading && canAnnounce"
				title="下書き"
				:icon="menuIcon(-1)"
				@click="showDraft()" />

			<AppNavigationNew
				v-if="!loading && canAnnounce"
				:text="t('welcomapp', '新規アナウンス')"
				:disabled="false"
				button-id="new-welcomapp-button"
				button-class="icon-add"
				@click="newNote" />
			<AppNavigationItem
				v-if="!loading && canAnnounce"
				title="カテゴリーの設定"
				:text="t('welcomapp', 'カテゴリーの設定')"
				:disabled="false"
				button-id="categories-welcomapp-button"
				button-class="icon-add"
				@click="changeMode('categorySetting')" />
			<AppNavigationItem
				v-if="!loading && canAnnounce"
				title="タグの設定"
				:text="t('welcomapp', 'タグの設定')"
				:disabled="false"
				button-id="tags-welcomapp-button"
				button-class="icon-add"
				@click="changeMode('tagSetting')" />
			<AppNavigationItem
				v-if="!loading && canAnnounce"
				title="ヘッダの設定"
				:text="t('welcomapp', 'ヘッダの設定')"
				:disabled="false"
				button-id="header-welcomapp-button"
				button-class="icon-add"
				@click="changeMode('headerSetting')" />
		</AppNavigation>
		<AppContent v-if="user && user.id">
			<div v-if="mode=='notes'">
				<Container :notes.sync="notes"
					class=""
					:current-note.sync="currentNote"
					:categories="categories"
					:tags="tags"
					:user="user"
					:users="users"
					:mode.sync="containerMode"
					:header-config="headerConfig"
					:filter.sync="filter" />
			</div>
			<div v-if="mode=='categorySetting'">
				<CategorySetting :categories.sync="categories" />
			</div>
			<div v-if="mode=='tagSetting'">
				<TagSetting :tags.sync="tags" />
			</div>
			<div v-if="mode=='headerSetting'">
				<HeaderEdit :user="user" :header-config.sync="headerConfig" />
			</div>

			<div v-if="!notes" id="emptycontent">
				<div class="icon-file" />
				<h2>{{ t("welcomapp", "表示するアイテムはありません。") }}</h2>
			</div>
		</AppContent>
	</div>
</template>

<script>
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppNavigationNew from '@nextcloud/vue/dist/Components/AppNavigationNew'

// import '@nextcloud/dialogs/styles/toast.scss'
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import { v4 as uuidv4 } from 'uuid'

import Container from './components/Container.vue'
import CategorySetting from './components/CategorySetting.vue'
import TagSetting from './components/TagSetting.vue'
import HeaderEdit from './components/HeaderEdit.vue'
import Mymodules from './js/modules'

export default {
	name: 'App',
	components: {
		AppContent,
		AppNavigation,
		AppNavigationItem,
		AppNavigationNew,
		Container,
		CategorySetting,
		TagSetting,
		HeaderEdit,
	},
	data() {
		return {
			notes: [],
			currentNoteId: null,
			updating: false,
			loading: true,
			content: '',
			currentNote: {},
			mode: 'notes',
			categories: [],
			selectedCategory: 0,
			tags: [],
			user: {},
			containerMode: 'list',
			users: [],
			testData: null,
			filter: { category: 0, pubFlag: true, pinFlag: false, offset: 0, limit: 0 },
			headerConfig: {},
		}
	},
	computed: {
		canAnnounce() {
			if (!this.user || !this.user.groups) { return false }
			return this.user.groups.includes('announce')
		},

	},
	watch: {

	},
	/**
	 * Fetch list of notes when the component is loaded
	 */
	async created() {
		// this.fetchNotes()
		this.fetchUserData()
		this.fetchCategories()
		this.fetchTags()

		axios.get(generateUrl('/apps/welcomapp/getusers')).then((result) => {
			this.users = result.data
		})
		this.loading = false

	},
	methods: {
		menuIcon(categoryId) {
			if (categoryId === this.selectedCategory) {
				return 'icon-triangle-e'
			} else {
				return ''
			}

		},
		async fetchNotes() {
			await axios.get(generateUrl('/apps/welcomapp/notes')).then((result) => {
				this.notes = result.data
			}).catch((e) => {
				console.error(e)
				showError(t('welcomapp', 'Could not fetch notes'))
				this.notes = []
			})
		},
		async fetchUserData() {

			axios.get(generateUrl('/apps/welcomapp/users')).then((result) => {
				this.user = result.data
				if (this.user.id) {

					if (this.user.groups.includes('announce')) {
						console.info(this.user)
						Mymodules.fetchDirInfoOrCreate(`${this.user.id}/announce_${this.user.id}`).then(() => {
							const data = { path: `announce_${this.user.id}`, shareType: 1, shareWith: 'all_users', publicUpload: 'false', permissions: 1 }
							 axios.post('/ocs/v2.php/apps/files_sharing/api/v1/shares', data, { headers: { 'OCS-APIRequest': true } }).then((result2) => {
								const shareId = result2?.data?.ocs?.data?.id
								if (shareId) {
									this.$set(this.user, 'shareId', shareId)
								}
		 }).catch((e) => {
								console.info('shareAPIerror')
							 axios.get('/ocs/v2.php/apps/files_sharing/api/v1/shares', data, { headers: { 'OCS-APIRequest': true } }).then((result3) => {
									const tmpArray = result3?.data?.ocs?.data
									console.info(tmpArray)
									if (tmpArray && tmpArray.length) {
										const target = tmpArray.find((elem) => elem.file_target === `/announce_${this.user.id}`)
										if (target) {
											console.info('target')
											console.info(target)
											this.$set(this.user, 'shareId', target.id)

										}
									}

								})
		 })

		 })
					}
					this.fetchHeader()
				}
			}).catch((e) => console.error(e))
		},
		async fetchCategories() {
			try {
				const response = await axios.get(generateUrl('/apps/welcomapp/categories'))
				this.categories = response.data
			} catch (e) {
				console.error(e)
				showError(t('welcomeapp', 'Could not fetch categories'))

			}

		},
		async fetchTags() {
			try {
				const response = await axios.get(generateUrl('/apps/welcomapp/tags'))
				this.tags = response.data
			} catch (e) {
				showError(t('welcomapp', 'Could not fetch tags'))
			}
		},
		async fetchHeader() {
			if (this.user.id) {
				Mymodules.fetchHeader(this.user.id).then((result) => {
					this.headerConfig = result
				})

			}
			try {
				 axios.get(generateUrl('/apps/welcomapp/getconfig/header')).then((result) => {
					if (result.data && result.data.length) {

						const tmpData = result.data[0]
						console.info(tmpData)
						if (tmpData.value) {
							let s = tmpData.value
							// preserve newlines, etc - use valid JSON
							s = s.replace(/\\n/g, '\\n')
								.replace(/\\'/g, "\\'")
								.replace(/\\"/g, '\\"')
								.replace(/\\&/g, '\\&')
								.replace(/\\r/g, '\\r')
								.replace(/\\t/g, '\\t')
								.replace(/\\b/g, '\\b')
								.replace(/\\f/g, '\\f')

							// remove non-printable and other non-valid JSON chars
							// s = s.replace(/[\u0000-\u0019]+/g, '')
							tmpData.value = JSON.parse(s)
						}
						return tmpData
					} else {
						return {}
					}
				}).then((data) => {
					// this.headerConfig = data
					console.info(data)
				})

			} catch (e) {
				showError('Could not fetch Header')
			}
		},
		/**
		 * Create a new note and focus the note content field automatically
		 * The note is not yet saved, therefore an id of -1 is used until it
		 * has been persisted in the backend
		 */
		newNote() {
			if (this.mode !== 'notes') {
				this.mode = 'notes'
				this.currentNote = { id: -2 }
				this.notes = this.notes.filter((note) => note.id !== -1)
			}
			if (this.currentNote.id !== -1) {
				const blankNote = {
					id: -1,
					title: '新規アナウンス',
					categories: 0,
					content: '',
					pinFlag: false,
					pubFlag: false,

					uuid: uuidv4(),
					shareId: this.user.shareId,
				}
				this.currentNoteId = -1
				this.$nextTick(() => {
					this.notes.push(blankNote)
					this.currentNote = blankNote
					this.content = ''

				})

			}
		},
		/**
		 * Abort creating a new note
		 */
		cancelNewNote() {
			this.notes.splice(
				this.notes.findIndex((note) => note.id === -1),
				1
			)
			this.currentNoteId = null
			this.currentNote = null
			this.content = null
		},
		changeMode(mode) {
			if (mode === 'notes') {
				this.containerMode = 'list'
			}
			this.currentNote = {}
			this.currentNoteId = null
			this.mode = mode
		},
		changeCategory(categoryId) {
			this.selectedCategory = categoryId
			this.filter = { category: categoryId, pubFlag: true, pinFlag: false, offset: 0, limit: 10 }
			this.changeMode('notes')
		},
		showDraft() {
			this.selectedCategory = -1
			this.filter = { category: 0, pubFlag: false, pinFlag: false, offset: 0, limit: 0 }
			this.changeMode('notes')
		},
	},
}
</script>
<style scoped>
#app-content > div {
	width: 100%;
	height: 100%;
	padding: 20px;
	display: flex;
	flex-direction: column;
	flex-grow: 1;
}

input[type='text'] {
	width: 100%;
}

textarea {
	flex-grow: 1;
	width: 100%;
}
</style>
