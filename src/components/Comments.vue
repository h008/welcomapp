<template>
	<div class="comments__wrapper">
		この投稿へのコメント
		<div v-for="commentItem,index of commentList" :key="index">
			<CommentItem :comment="commentItem"
				:user-id="user.id"
				:author-id="authorId"
				:is-admin="isAdmin"
				@removecomment="removeComment" />
		</div>
		<input v-model="comment" type="text" class="comments__input">
		<button class="comments__button" :disabled="!comment" @click="putComments(comment)">
			<SendIcon />
		</button>
	</div>
</template>

<script>
import Mymodules from '../js/modules'
import CommentItem from './CommentItem.vue'
import SendIcon from 'vue-material-design-icons/Send.vue'
export default {
	name: 'Comments',
	components: {
		CommentItem,
		SendIcon,

	},
	props: {
		shareId: {
			type: String,
			default: '',
		},
		fileId: {
			type: String,
			default: null,
		},
		user: {
			type: Object,
			default: () => { return {} },
		},
		authorId: {
			type: String,
			default: '',
		},
		isAdmin: {
			type: Boolean,
			default: false,

		},
	},
	data() {
		return {

			comment: '',
			commentList: [],
		}
	},
	watch: {
		shareId() {
			this.fetcComments()
		},

	},
	mounted() {
		this.fetchComments()

	},

	methods: {
		fetchComments() {
			if (!this.fileId) { return }
			Mymodules.fetchSharedComments(this.fileId).then((result) => {
				this.commentList = result
			}).catch((e) => console.info(e))
		},
		putComments(comment) {
			if (!this.fileId || !this.user.id || !comment) { return }
			Mymodules.putSharedComments(this.user.id, this.fileId, comment).then(() => {
				this.fetchComments()
			})

		},
		removeComment(comment) {
			if (confirm('削除しますか？')) {
				Mymodules.removeSharedComments(this.fileId, comment.id).then(() => {
					this.fetchComments()
				})
			}

		},
	},

}
</script>
<style scoped>
.comments__wrapper {
	max-width:800px;
	margin:0 auto;
}

.comments__input {
	width:calc(100% - 100px);
}

.comments__button {
	width:80px;

}
</style>
