<template>
	<div v-if="note && note.id " class="pincard__wrapper">
		<div class="pincard__outline">
			<div class="pincard__eyecatch" :style="{'background-color':categorySeal.color}">
				<img v-if="eyecatchUrl" :src="eyecatchUrl">
			</div>
			<div v-if="!note.isRead" class="pincard__isunread">
				<div class="pincard__isunread_mark">
					未読
				</div>
			</div>
			<div class="pincard__content">
				<div class="pincard__title">
					{{ note.title }}
				</div>
				<h2 v-if="categorySeal" :style="{'color':categorySeal.color ,'font-weight':600}">
					{{ categorySeal.category_name }}
				</h2>
				<div class="tags__flex">
					<TagBadges :tags="tags" :display-tag-ids="note.tags" />
					<GroupBadge :groups="note.shareGroups" />
					<div v-if="hasFiles" class="pincard__attachment">
						<AttachmentIcon title="添付あり" />添付あり
					</div>
				</div>
				<div v-if="note && note.userInfo" class="infobox">
					<div>作成:{{ note.created }}</div>
					<div>更新:{{ note.updated }}</div>
					<div>投稿:{{ note.userInfo.displayname }}</div>
				</div>
				<div class="pincard__text">
					{{ summary }}
				</div>

				<div class="pincard__link">
					<a @click="showDetail">詳しく見る</a>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
import TagBadges from './TagBadges.vue'
import GroupBadge from './GroupBadge.vue'
import AttachmentIcon from 'vue-material-design-icons/Attachment.vue'
export default {
	name: 'PinCard',
	components: {
		TagBadges,
		GroupBadge,
		AttachmentIcon,
	},
	props: {
		note: {
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
		updated: {
			type: Array,
			default: () => { return [] },
		},
		index: {
			type: Number,
			default: 0,
		},
	},
	data() {
		return {
			auther: {},
		}
	},
	computed: {

		summary() {
			if (this.note.content) {
				const str = this.note.content.replace(/<("[^"]*"|'[^']*'|[^'">])*>|\s|&nbsp/g, '')

				return str.substr(0, 250)
			} else {
				return ''
			}
		},
		categorySeal() {
			const category = this.categories.find((element) => Number(element.id) === Number(this.note.category))
			return category
		},
		eyecatchUrl() {
			if (this.updated.length > this.index + 1) {

				if (!this.note.fileInfo || !this.note.fileInfo.length) {
					return ''
				}
				const fileInfo = this.note.fileInfo.find((item) => {
					if (!item.is_eyecatch || (item.is_eyecatch && (item.is_eyecatch === 0 || item.is_eyecatch === '0' || item.is_eyecatch === 'false' || item.is_eyecatch === ''))) {
						return false
					} else {
						return true
					}
				})
				if (!fileInfo) {
					return ''
				}
				return fileInfo.userRef || ''
			} else {
				return ''
			}
		},
		hasFiles() {
			if (!this.note.fileInfo || !this.note.fileInfo.length) {
				return false
			}
			const fileInfo = this.note.fileInfo.find((item) => (!item.is_eyecatch || Number(item.is_eyecatch) !== 1))
			if (fileInfo) { return true } else { return false }
		},
	},
	watch: {
	},
	methods: {
		showDetail(event) {
			this.$emit('click', event)
		},

	},

}
</script>
<style scoped>
.tags__flex{
	width:100%;
	display:flex;
}

.pincard__wrapper{
	width: 350px;
	min-width:350px;
	margin:4px;
}

.pincard__wrapper:hover{
	transform: translateY(-5px);
	box-shadow:0 7px 34px rgba(50,50,93,.1),0 3px 6px rgba(0,0,0,.8);
	transition:all .5s;
}

.pincard__outline{
	height:100%;
	background:#fff;
	border-radius:5px;
	box-shadow:0 2px 5px #ccc;
	position:relative;
	padding-bottom:50px;
}

.pincard__eyecatch{
	min-height:160px;
}

.pincard__eyecatch img{
	border-radius:5px 5px 0 0;
	max-width:100%;
	height:auto;
}

.pincard__content{
	padding:8px 20px 20px;
}

.pincard__title{
	padding: 1rem 1.5rem 0;
	font-size: 1.6rem;
	order: 1;
	font-weight: bold;
	text-decoration: none;
	/*線の種類（実線） 太さ 色*/
	border-bottom: solid 3px black;

}

.pincard__text{
	color:#777;
	font-size:14px;
	line-height:1.5;
	margin-bottom:0.23em;
	margin-top:0.23em;
}

.pincard__link{
	position:absolute;
	bottom:5px;
	text-align:center;
	border-top:1px solid #eee;
	padding:20px;
}

.pincard__link a{
	text-decoration:none;
	color:#4f96f6;
	margin: 0 10px;
}

.pincard__link a:hover{
	color:#6bb6ff;
}

.pincard__content p {
	margin-bottom:0;
}

.infobox{
	text-align: end;
}

.pincard__attachment{
	display:flex;
	text-align:end;
	vertical-align: middle;

}

.pincard__isunread {
	position:absolute;
	top:-2px;
	right:-2px;
}

.pincard__isunread_mark {
	background-color:gold;
	border: lightblue solid 1px;
	border-radius: 50px;
	padding-inline:8px;
	font-weight: 600;
}
</style>
