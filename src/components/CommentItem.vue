<template>
	<div class="commentitem__wrapper">
		<div :class="whoClass">
			<Avatar :user="comment.actorId" :display-name="comment.actorDisplayName" />
			<div class="chat">
				<div class="message">
					<p>{{ comment.message }}</p>
				</div>
				<div class="messageinfo">
					{{ comment.actorDisplayName }}{{ comment.creationDateTime }}
					<span v-if="comment.actorId==userId" class="removebtn" @click="removeComment(comment)">削除する</span>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import Avatar from '@nextcloud/vue/dist/Components/Avatar'
export default {
	name: 'CommentItem',
	components: {
		Avatar,
	},
	props: {
		comment: {
			type: Object,
			default: () => { return {} },
		},
		userId: {
			type: String,
			default: '',
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
	computed: {

		whoClass() {
			switch (this.comment.actorId) {
			case this.userId:
				if (this.userId === this.authorId) {
					return 'self author'
				}
				return 'self who'
			case this.authorId:
				return 'author'
			default:
				return 'others who'
			}

		},
	},
	methods: {
		removeComment(comment) {
			this.$emit('removecomment', comment)
		},
	},

}
</script>

<style scoped>

.commentitem__wrapper {
	width: 100%;
	margin: 1em 0;
	overflow: hidden;
}

.commentitem__wrapper .who {
	display:flex;
	float:right;
}

.commentitem__wrapper .who .avatardiv {
	float: right;
	margin-right:4px;
	margin-left: 20px;
}

.commentitem__wrapper .author .avatardiv{
	float: left;
	margin-right: 20px;
}

.commentitem__wrapper .chat {
	width: 100%;
}

.message {
	display: inline-block;
	position: relative;
	padding: 7px 10px;
	border-radius: 12px;
	background: #d7ebfe;
}

.author .message {
	margin: 5px 0;
}

.message p {
	margin: 0;
	padding: 0;
}

.messageinfo {
	font-size:0.9rem;
}

.author .messageinfo {
	margin:0 0 0 auto;
}

.self .chat .message {
	background: #30e852;
}

.author .chat .message:after {
	content: '';
	position: absolute;
	top: 8px;
	left: -8px;
	border: 8px solid transparent;
	border-left: 12px solid #d7ebfe;
	-webkit-transform: rotate(-35deg);
	transform: rotate(-35deg);
}

.who .chat {
	order:1;
	text-align:right;
}

.who .avatardiv {
	order:2;
}

.who .chat .message:after {
	content: '';
	position: absolute;
	top: 8px;
	right: -8px;
	border: 8px solid transparent;
	-webkit-transform: rotate(-125deg);
	transform: rotate(-125deg);

}

.others .chat .message:after {
	border-left: 12px solid #d7ebfe;
}

.self .chat .message:after {
	border-left: 12px solid #30e852;
}
</style>
