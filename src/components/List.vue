<template>
	<div>
		<div v-if="loading">
			<Loader />
		</div>
		<div v-else-if="notes && notes.length">
			<ul>
				<ListItem v-for="item in notes"
					:key="item.id"
					:title="item.title"
					:bold="true"
					:details="item.updated"
					@click.stop="showDetail(item)">
					<template #icon>
						<div v-if="!item.isRead" class="list__unread">
							<UnreadIcon />
						</div>
						<div v-if="item.isRead" class="list__unread">
							<ReadIcon />
						</div>
					</template>
					<template #subtitle>
						<div class="d-block">
							<div class="d-block">
								{{ striptag(item.content) }}
							</div>
							<div class="d-flex">
								<TagBadges :tags="tags" :display-tag-ids="item.tags" />
								<GroupBadge :groups="item.shareGroups" />
								<div v-if="hasFiles(item)" class="list__attachment">
									<AttachmentIcon title="添付あり" />添付あり
								</div>
								<div>
									既読:{{ readUsersCount(item.readusers) }}
								</div>
							</div>
						</div>
					</template>
					<template #actions>
						<ActionButton icon="icon-edit" @click.stop="showDetail(item)">
							詳細をみる
						</ActionButton>
						<ActionButton v-if="canAnnounce(item)" icon="icon-rename" @click.stop="openDialog(item)">
							編集する
						</ActionButton>
						<ActionButton v-if="canAnnounce(item)" icon="icon-delete" @click.stop="deleteAnnounce(item)">
							削除する
						</ActionButton>
					</template>
				</ListItem>
			</ul>
			<Pagination v-if="paginationTotal" :total="paginationTotal" :current.sync="currentIndex" />
		</div>
		<div v-else>
			<EmptyContent icon="icon-comment">
				表示するお知らせはありません。
				<template #desc>
					すべてのカテゴリから新着順に表示するには「新着順」を選択してください
				</template>
			</EmptyContent>
		</div>
	</div>
</template>
<script>
import ListItem from '@nextcloud/vue/dist/Components/ListItem'
import Pagination from './Pagination.vue'
import Loader from './Loader.vue'
import TagBadges from './TagBadges.vue'
import GroupBadge from './GroupBadge.vue'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import EmptyContent from '@nextcloud/vue/dist/Components/EmptyContent'
import AttachmentIcon from 'vue-material-design-icons/Attachment.vue'
import UnreadIcon from 'vue-material-design-icons/EmailAlert.vue'
import ReadIcon from 'vue-material-design-icons/EmailOpenOutline.vue'

import Modules from '../js/modules'
import { showError } from '@nextcloud/dialogs'
export default {
	name: 'List',
	components: {
		ListItem,
		ActionButton,
		Pagination,
		EmptyContent,
		Loader,
		TagBadges,
		GroupBadge,
		AttachmentIcon,
		UnreadIcon,
		ReadIcon,
	},
	props: {
		notes: {
			type: Array,
			default: () => { return [] },
		},
		currentNote: {
			type: Object,
			default: () => { return { id: -1, title: '', content: '' } },
		},
		dialog: {
			type: Boolean,
			default: false,
		},
		user: {
			type: Object,
			default: () => { return {} },
		},
		mode: {
			type: String,
			default: 'list',
		},
		filter: {
			type: Object,
			default: () => {
				return { category: 0, pubFlag: 1, pinFlag: 0, offset: 0, limit: 0, tags: 'all', unread: 0 }

			},

		},
		tags: {
			type: Array,
			default: () => { return [] },
		},
		allGroups: {
			type: Array,
			default: () => { return [] },
		},
	},
	data() {
		return {
			currentIndex: 1,
			totalNotesNumber: 0,
			loading: false,
		}
	},
	computed: {
		localListItem: {
			get() { return this.notes },
			set(val) { this.$emit('update:notes', val) },
		},
		localCurrentNote: {
			get() { return this.currentNote },
			set(val) { this.$emit('update:currentNote', val) },
		},
		paginationTotal() {
			if (!this.filter.limit) { return 1 }
			return Math.ceil(this.totalNotesNumber / this.filter.limit)
		},
		localFilter: {
			get() {
				return this.filter
			},
			set(val) {
				console.info(val)
				this.$emit('update:filter', val)
			},

		},

	},
	watch: {
		mode() {
			this.localListItem = Array.from(this.notes)
			// console.info(this.localListItem)
		},
		filter() {
			this.fetchNotes()
		},
		dialog(val) {
			if (!val) {
				this.fetchNotes()
			}
		},
		currentIndex(val) {
      console.info(val)
			const offset = (val - 1) * this.localFilter.limit
			this.$set(this.localFilter, 'offset', offset)
		},

	},
	mounted() {
		this.fetchNotes()

	},
	methods: {
		readUsersCount(usersCsv) {
			if (!usersCsv) { return 0 }
			const tmpArray = usersCsv.split(',')

			if (!tmpArray || !Array.isArray(tmpArray) || !tmpArray.length) {
				return 0
			}
			const filteredArray = tmpArray.filter((user) => (user))
			return filteredArray.length

		},
		hasFiles(note) {
			if (!note.fileInfo || !note.fileInfo.length) {
				return false
			}
			const fileInfo = note.fileInfo.find((item) => (!item.is_eyecatch || Number(item.is_eyecatch) !== 1))
			if (fileInfo) { return true } else { return false }
		},
		canAnnounce(item) {
			if (!this.user || !this.user.groups) { return false }
			return this.user.groups.includes('announce') && item.userId === this.user.id
		},
		fetchNotes() {
			this.loading = true
			Modules.fetchNotes(this.user, this.filter).then((result) => {
				if (result && result.data && result.data.length) {
					this.localListItem = result.data
					this.totalNotesNumber = result.total
				} else {
					this.localListItem = []
					this.totalNotesNumber = 0
				}
				this.loading = false
			})
		},
		striptag(html) {
			if (html) {
				const str = html.replace(/<("[^"]*"|'[^']*'|[^'">])*>|\s|&nbsp;/g, '')

				return str.substr(0, 100)
			} else {
				return ''
			}
		},
		selectCurrent(item) {
			this.localCurrentNote = item
		},
		clickItem(item) {

			if (item.id === this.localCurrentNote.id) {
				this.localCurrentNote = {}
			} else {
				this.localCurrentNote = item
			}
		},
		openDialog(item) {
			this.localCurrentNote = Object.assign({}, item)
			this.$emit('update:currentNote', item)
			this.$emit('update:dialog', true)
		},
		deleteAnnounce(item) {
			if (confirm('本当に削除しますか?')) {
				Modules.deleteNote(item).then(() => {
					const tmpArray = this.notes.filter((note) => note.id !== item.id)
					this.$emit('update:notes', tmpArray)

				}).catch((e) => {
					showError(e)
				})

			}

		},
		showDetail(item) {
			this.localCurrentNote = Object.assign({}, item)
			this.$emit('update:mode', 'detail')
			this.$emit('update:dialog', false)
		},
	},

}
</script>
<style>
.d-flex{
	display: flex;
	float:  left;
}

.d-block{
	display:block;
}

.title{
	margin-inline:8px;
	text-align:start;
	font-weight: 700;
	max-width: 100ch;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

.description{
	text-align: start;
	font-size:small;
	max-width: 100ch;
	min-width: 10ch;
	overflow:hidden;
	text-overflow:ellipsis;
	white-space:nowrap;
}

.updated{
	text-align: end;
	width:20ch;
}

.list__attachment{
	display:flex;
	text-align:end;
	vertical-align: middle;
}

</style>
