<template>
	<div v-if="note && note.id " class="pincard__wrapper">
		<div class="pincard__outline">
			<div class="pincard__eyecatch" :style="{'background-color':categorySeal.color}">
				<img v-if="eyecatchUrl" :src="eyecatchUrl">
			</div>
			<div class="pincard__content">
				<div class="pincard__title">
					{{ note.title }}
				</div>
				<h2 v-if="categorySeal" :style="{'color':categorySeal.color ,'font-weight':600}">
					{{ categorySeal.category_name }}
				</h2>
				<TagBadges :tags="tags" :display-tag-ids="note.tags" />
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
export default {
	name: 'PinCard',
	components: {
		TagBadges,
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
	},
	data() {
		return {
			auther: {},
		}
	},
	computed: {

		summary() {
			if (this.note.content) {
				const str = this.note.content.replace(/<("[^"]*"|'[^']*'|[^'">])*>/g, '')

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
			if (!this.note.fileInfo || !this.note.fileInfo.length) {
				return ''
			}
			const fileInfo = this.note.fileInfo.find((item) => item.is_eyecatch)
			return fileInfo.userRef
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
</style>
