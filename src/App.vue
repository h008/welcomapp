<template>
	<div id="content" class="app-welcomapp">
		<AppNavigation>
			<div class="nav__wrapper">
				<TagSelector :tags="tags" :filter.sync="filter" />
				<AppNavigationItem
					v-if="!loading"
					title="新着順"
					:icon="menuIcon(0)"
					@click="changeCategory(0)" />
				<AppNavigationItem
					v-if="!loading"
					title="未読"
					:icon="menuIcon(9999)"
					@click="selectUnread" />
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
					v-if="!loading && isAdmin"
					title="カテゴリーの設定"
					:text="t('welcomapp', 'カテゴリーの設定')"
					:disabled="false"
					button-id="categories-welcomapp-button"
					button-class="icon-add"
					@click="changeMode('categorySetting')" />
				<AppNavigationItem
					v-if="!loading && isAdmin"
					title="タグの設定"
					:text="t('welcomapp', 'タグの設定')"
					:disabled="false"
					button-id="tags-welcomapp-button"
					button-class="icon-add"
					@click="changeMode('tagSetting')" />
				<AppNavigationItem
					v-if="!loading && isAdmin"
					title="ヘッダの設定"
					:text="t('welcomapp', 'ヘッダの設定')"
					:disabled="false"
					button-id="header-welcomapp-button"
					button-class="icon-add"
					@click="changeMode('headerSetting')" />
				<AppNavigationItem
					v-if="!loading && isAdmin"
					title="グループ名の設定"
					:text="t('welcomapp', 'グループ名の設定')"
					:disabled="false"
					button-id="groups-welcomapp-button"
					button-class="icon-add"
					@click="changeMode('groupSetting')" />
			</div>
		</AppNavigation>
		<AppContent v-if="user && user.id">
			<div v-if="mode=='notes'">
				<Container :notes.sync="notes"
					class=""
					:current-note.sync="currentNote"
					:categories="categories"
					:tags="tags"
					:user="user"
					:all-users="allUsers"
					:all-groups="allGroups"
					:mode.sync="containerMode"
					:header-config="headerConfig"
					:filter.sync="filter"
					:selected-category="selectedCategory"
					:is-admin="isAdmin" />
			</div>
			<div v-if="mode=='categorySetting' && isAdmin">
				<CategorySetting :categories.sync="categories" />
			</div>
			<div v-if="mode=='tagSetting' && isAdmin">
				<TagSetting :tags.sync="tags" />
			</div>
			<div v-if="mode=='headerSetting' && isAdmin">
				<HeaderEdit :user="user" :header-config.sync="headerConfig" />
			</div>
			<div v-if="mode=='groupSetting' && isAdmin">
				<GroupEdit :user="user" :all-groups="allGroups" />
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
import { showError } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import { v4 as uuidv4 } from 'uuid'

import Container from './components/Container.vue'
import CategorySetting from './components/CategorySetting.vue'
import TagSetting from './components/TagSetting.vue'
import HeaderEdit from './components/HeaderEdit.vue'
import TagSelector from './components/TagSelector.vue'
import GroupEdit from './components/GroupEdit.vue'
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
		GroupEdit,
		TagSelector,
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
			testData: null,
			filter: { category: 0, pubFlag: 1, pinFlag: 0, offset: 0, limit: 0, tags: 'all', unread: 0 },
			headerConfig: {},
			allUsers: [],
			allGroups: [],
		}
	},
	computed: {
		canAnnounce() {
			if (!this.user || !this.user.groups) { return false }
			return this.user.groups.includes('announce')
		},
		isAdmin() {
			if (!this.user || !this.user.groups) { return false }
			return this.user.groups.includes('webmaster')
		},

	},
	watch: {
		filter(val) {
			console.info(val)
		},

	},
	/**
	 * Fetch list of notes when the component is loaded
	 */
	async created() {
		// this.fetchNotes()
		this.fetchUserData()
		this.fetchCategories()
		this.fetchTags()

		axios.get(generateUrl('/apps/welcomapp/getallusers')).then((result) => {
			this.allUsers = result.data
		})
		axios.get(generateUrl('/apps/welcomapp/getallgroups')).then((result) => {
			if (result && result.data) {

				this.allGroups = result.data.filter((group) => group.id !== 'admin')
			}
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
				// console.info(result.data)
				this.user = result.data
				if (this.user.id) {

					/*
					if (this.user.groups.includes('announce')) {
						//console.info(this.user)
						Mymodules.fetchDirInfoOrCreate(`${this.user.id}/announce_${this.user.id}`).then(() => {
							 axios.post('/ocs/v2.php/apps/files_sharing/api/v1/shares', data, { headers: { 'OCS-APIRequest': true } }).then((result2) => {
								const shareId = result2?.data?.ocs?.data?.id
								if (shareId) {
									this.$set(this.user, 'shareId', shareId)
								}
		 }).catch((e) => {
								//console.info('shareAPIerror')
							 axios.get('/ocs/v2.php/apps/files_sharing/api/v1/shares', data, { headers: { 'OCS-APIRequest': true } }).then((result3) => {
									const tmpArray = result3?.data?.ocs?.data
									//console.info(tmpArray)
									if (tmpArray && tmpArray.length) {
										const target = tmpArray.find((elem) => elem.file_target === `/announce_${this.user.id}`)
										if (target) {
											//console.info('target')
											//console.info(target)
											this.$set(this.user, 'shareId', target.id)

										}
									}

								})
		 })
		 })
					}
*/
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
					pinFlag: 0,
					pubFlag: 0,

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
			const tmpFilter = Object.assign({}, this.filter)
			tmpFilter.category = categoryId
			tmpFilter.pubFlag = 1
			tmpFilter.pinFlag = 0
			tmpFilter.offset = 0
			tmpFilter.limit = 10
			tmpFilter.unread = 0
			// const tmpFilter = Object.assign(this.filter, { category: categoryId, pubFlag: 1, pinFlag: 0, offset: 0, limit: 10 })
			this.filter = tmpFilter
			this.changeMode('notes')
		},
		selectUnread() {
			this.selectedCategory = 9999
			const tmpFilter = Object.assign({}, this.filter)
			tmpFilter.category = 0
			tmpFilter.pubFlag = 1
			tmpFilter.pinFlag = 0
			tmpFilter.offset = 0
			tmpFilter.limit = 10
			tmpFilter.unread = 1
			// const tmpFilter = Object.assign(this.filter, { category: categoryId, pubFlag: 1, pinFlag: 0, offset: 0, limit: 10 })
			this.filter = tmpFilter
			this.changeMode('notes')

		},
		showDraft() {
			this.selectedCategory = -1
			const tmpFilter = Object.assign({}, this.filter)
			tmpFilter.category = 0
			tmpFilter.pubFlag = 0
			tmpFilter.pinFlag = 0
			tmpFilter.offset = 0
			tmpFilter.limit = 0
			tmpFilter.unread = 0
			// const tmpFilter = Object.assign(this.filter, { category: 0, pubFlag: 0, pinFlag: 0, offset: 0, limit: 0 })
			this.filter = tmpFilter
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

.nav__wrapper {
	max-height: 100vh;
	min-height:100%;
	overflow-y:scroll;
}

</style>
