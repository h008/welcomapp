<template>
	<div class="header__wrapper">
		<div class="header_body">
			<transition-group :name="transition_name">
				<img v-for="(content,index) in headerConfig.images"
					v-show="isVisible(index)"
					:key="`image_${index}`"
					class="header_img"
					:class="isVisible(index)"
					:src="content">
			</transition-group>
		</div>
		<div v-if="headerConfig && headerConfig.value && headerConfig.value.title" class="welcom_title">
			<div class="title_text">
				{{ headerConfig.value.title }}
			</div>
		</div>
		<div class="welcom_footer">
			<div v-for="(content, index) in headerConfig.images"
				:key="`dot_${index}`"
				class="welcom_footer_dot"
				:class="isVisible(index)" />
		</div>
	</div>
</template>
<script>
export default {
	name: 'Header',
	props: {
		headerConfig: {
			type: Object,
			default: () => {
				return {}
			},
		},
	},
	data() {
		return {
			img: '',
			title: 'タイトル',
			transition_name: 'show-next',
			visible_content: 0,
		}
	},
	mounted() {
		setInterval(() => {
			if (this.headerConfig && this.headerConfig.images && this.headerConfig.images.length) {
				this.visible_content = (this.visible_content + 1) % (this.headerConfig.images.length)
			}
		}, 4000)

	},
	methods: {
		back() {
			this.transition_name = 'show-prev'
			this.visible_content -= 1

		},
		next() {
			this.transition_name = 'show-next'
			this.visible_content += 1
		},
		isVisible(index) {
			if (index === this.visible_content) {
				return 'is-visible'
			} else {
				return ''
			}
		},
	},
}
</script>
<style scoped lang="scss">
.header__wrapper{
	height:300px;
	overflow:hidden;
	position:relative;
	width:100%;
	.header_body{

		.header_img{
			color:#fff;
			height:300px;
			left:0;
			line-height:150px;
			position:absolute;
			text-align:center;
			top:0;
			width:100%;

		}
		.is-visible{
			background-color:#7b94f9;
		}
	}
	.welcom_title{
		width:100%;
		height:100%;
		background-color:white;
		opacity:0.4;
		.title_text{
			position:absolute;
			top:50%;
			left:50%;
			font-size: 3rem;
			font-weight:bold;
			vertical-align:center;
			text-align: center;
			-ms-transform: translate(-50%,-50%);
			-webkit-transform: translate(-50%,-50%);
			transform: translate(-50%,-50%);
			margin:0;
			padding:20px;
		}

	}
	.welcom_footer{
		align-items:center;
		display:flex;
		height:50px;
		justify-content:space-between;
		position:absolute;
		bottom:20px;
		width:50%;
		.welcom_footer_dot{
			background-color:#abc2ce;
			border-radius:50%;
			height:6px;
			width:6px;
		}
		.is-visible{
			background-color:#7b94f9;
		}
	}
}

.show-next-enter-active, .show-next-leave-active, .show-prev-enter-active, .show-prev-leave-active {
	transition:all .6s;

}

.show-next-enter, .show-prev-leave-to{
	transform:translateX(100%);
}

.show-next-leave-to, .show-prev-enter{
	transform:translateX(-100%);
}

</style>
