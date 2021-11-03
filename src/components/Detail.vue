<template>
	<div class="detail__wrapper">
		<div class="detail__eyecatch" :style="{'background-color':categorySeal.color}">
			<img :src="eyecatchUrl">
			<div class="detail__title">
				<h1>{{ note.title }}</h1>
				<div>
					<h2 v-if="categorySeal" :style="{'color':categorySeal.color }">
						カテゴリー:<span :style="{'color':categorySeal.color}">
							{{ categorySeal.category_name }}</span>
					</h2>
				</div>
			</div>
		</div>
		<div class="detail__content">
			<div class="d-flex">
				<TagBadges :tags="readTags" :display-tag-ids="isRead" />
				<TagBadges :tags="tags" :display-tag-ids="note.tags" />
				<GroupBadge :groups="note.shareGroups" />
			</div>
			<div v-if="note && note.userInfo" class="infobox">
				<div>作成:{{ note.created }}</div>
				<div>更新:{{ note.updated }}</div>
				<div>投稿:{{ note.userInfo.displayname }}</div>
			</div>
			<div class="detail__text">
				<SanitizedContent :content="note.content" />
			</div>
			<FileList v-if="note.fileInfo && note.fileInfo.length" :file-info="note.fileInfo" />
			<ReadUsers :read-users.sync="readUsers"
				:user="user"
				:is-read="isRead"
				:all-users="allUsers" />
		</div>
	</div>
</template>
<script>
import SanitizedContent from './SanitizedContent.vue'
import FileList from './FileList.vue'
import TagBadges from './TagBadges.vue'
import GroupBadge from './GroupBadge.vue'
import ReadUsers from './ReadUsers.vue'
import Mymodules from '../js/modules'
export default {
	name: 'Detail',
	components: {
		SanitizedContent,
		FileList,
		TagBadges,
		GroupBadge,
		ReadUsers,
	},
	props: {
		note: {
			type: Object,
			default: () => { return {} },
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
		allUsers: {
			type: Array,
			default: () => { return [] },
		},
	},
	data() {
		return {
			readTags: [
				{ id: 8888, color: 'gray', tag_name: '既読' },
				{ id: 9999, color: 'red', tag_name: '未読' },

			],
		}
	},
	computed: {
		categorySeal() {
			const category = this.categories.find((element) => Number(element.id) === Number(this.note.category))
			return category
		},
		eyecatchUrls() {
			// console.info('eyecatch')
			// console.info(this.note.fileInfo)
			if (!this.note.fileInfo || !this.note.fileInfo.length) {
				return ['']
			}
			return this.note.fileInfo.filter((file) => {
				if (!file.is_eyecatch || (file.is_eyecatch && (file.is_eyecatch === 0 || file.is_eyecatch === '0' || file.is_eyecatch === 'false' || file.is_eyecatch === ''))) { return false } else { return true }

			}).map((element) => element.userRef)

		},
		eyecatchUrl() {
			if (!this.eyecatchUrls || !this.eyecatchUrls.length) { return '' }
			return this.eyecatchUrls[0]
		},
		readUsers: {
			get() {
				return this.note.readusers.split(',').filter((el) => el)
			},
			set(val) {
				const tmpCsv = val.join(',')

				const tempNote = Object.assign(this.note, { readusers: tmpCsv })
				Mymodules.updateRead(tempNote).then(() => {
					this.$emit('update:note', tempNote)

				})
			},

		},
		isRead() {
			if (this.readUsers && this.readUsers.length) {
				if (this.readUsers.find((uid) => uid === this.user.id).length) {
					return '8888'
				}

			}
			return '9999'
		},
	},
}
</script>
<style scoped>
.detail__wrapper{
	padding:20px;
}

.detail__eyecatch{
	position:relative;
	width:100%;
	height:300px;
}

.detail__eyecatch img{
	width:100%;
	height:300px;
	object-fit:cover;
}

.detail__content {
	padding:40px;
}

.detail__title{
	position:absolute;
	width:100%;
	top:50%;
	left:50%;
	text-align: center;
	-ms-transform: translate(-50%,-50%);
	-webkit-transform: translate(-50%,-50%);
	transform: translate(-50%,-50%);
	margin:0;
	padding:20px;
	background-color:white;
	opacity:0.6;

}

.detail__title h1{
	/* padding: 1rem 1.5rem 0; */
	order: 1;
	font-weight: bold;
	text-decoration: none;
	/*線の種類（実線） 太さ 色*/
	border-bottom: solid 3px black;

}

.infobox{
	text-align: end;
}
</style>
