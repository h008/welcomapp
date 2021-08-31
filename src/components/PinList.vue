<template>
	<div>
		<Loader v-if="loading" />
		{{filter}}
		<div v-else-if="localList && localList.length" class="d-block">
			<div class="card__wrapper">
				<PinCard v-for="item in localList"
					:key="`pc__${item.id}`"
					:categories="categories"
					:tags="tags"
					:note="item"
					@click="showDetail(item)" />
			</div>
		</div>
	</div>
</template>
<script>
import PinCard from './PinCard.vue'
import Modules from '../js/modules'
import Loader from './Loader.vue'
import { showError } from '@nextcloud/dialogs'
export default {
	name: 'PinList',
	components: {
		PinCard,
		Loader,
	},
	props: {
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
		categories: {
			type: Array,
			default: () => { return [] },
		},
		tags: {
			type: Array,
			default: () => { return [] },
		},
		mode: {
			type: String,
			default: 'list',
		},
		filter: {
			type: Object,
			default: () => { return { category: 0, pinFlag: true, pubFlag: true } },
		},
	},
	data() {
		return {
			localList: [],
			itemNumber: 0,
			offset: 0,
			limit: 0,
			loading: false,
		}
	},
	computed: {
		localCurrentNote: {
			get() { return this.currentNote },
			set(val) { this.$emit('update:currentNote', val) },
		},
	},
	watch: {
		dialog(val) {
			if (!val) {
				this.init()
			}
		},
		filter() {
			this.init()
		},
	},
	mounted() {
		this.init()

	},
	methods: {
		init() {
			this.loading = true
			 Modules.fetchNotes(this.user.id, this.filter).then((data) => {
				const localList = data.data
				this.localList = localList
				this.itemNumber = data.total
				this.loading = false
			 }
			)

		},
		canAnnounce(item) {
			if (!this.user || !this.user.groups) { return false }
			return this.user.groups.includes('announce') && item.userId === this.user.id
		},
		striptag(html) {
			if (html) {
				const str = html.replace(/<("[^"]*"|'[^']*'|[^'">])*>/g, '')

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
					this.localList = this.localList.filter((note) => note.id !== item.id)
					// this.$emit('update:notes', tmpArray)

				}).catch((e) => {
					showError(e)
				})

			}

		},
		showDetail(item) {
			console.info('showDettail')
			this.localCurrentNote = Object.assign({}, item)
			this.$emit('update:mode', 'detail')
			this.$emit('update:dialog', false)
		},
	},

}
</script>
<style>
.card__wrapper{
	display:flex;
	flex-wrap:wrap;
	justify-content: flex-start;
	width:100%;
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
</style>
