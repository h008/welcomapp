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
			<div class="titles">
				<div class="title_text" :style="{'color':headerConfig.value.color}">
					{{ headerConfig.value.title }}
				</div>
				<div class="subtitle_text" :style="{'color':headerConfig.value.color}">
					{{ headerConfig.value.subTitle }}
				</div>
				<div class="message_text">
					{{ headerConfig.value.messageTxt }}
				</div>
			</div>
		</div>
		<div class="welcom_footer">
			<div class="welcom_fotter_dot_wrapper">
				<div v-for="(content, index) in headerConfig.images"
					:key="`dot_${index}`"
					class="welcom_footer_dot"
					:class="isVisible(index)" />
			</div>
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
			if (this.headerConfig && this.headerConfig.images && this.headerConfig.images.length > 1) {
				this.visible_content = (this.visible_content + 1) % (this.headerConfig.images.length)
			}
		}, 10000)

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
			height:auto;
			left:0;
			line-height:150px;
			position:absolute;
			text-align:center;
			top:0;
			width:100%;
			object-fit:cover;
			//opacity:0.0

		}
		//.is-visible{
		//opacity: 1.0;
		//}
	}
	.welcom_title{
		width:100%;
		height:100%;
		background-color:white;
		opacity:0.4;
		display:flex;
		justify-content:center;
		align-items:center;

		.titles{

			.title_text{
				line-height:1.2;
				font-size: 3rem;
				font-weight:bold;
				text-align: center;
				padding-bottom:8px;
				margin-bottom:10px;
				border-bottom:3px solid;

			}
			.subtitle_text{
				line-height:1.2;
				font-size:2rem;
				font-weight:bolder;
				text-align: center;
				margin-bottom:10px;
			}
			.message_text{
				line-height:1.5;
				font-size:1rem;
				text-align: center;

			}
		}

	}
	.welcom_footer{
		align-items:center;
		display:flex;
		height:50px;
		justify-content:center;
		bottom:10px;
		width:100%;
		.welcom_footer_dot_wrapper{
			width:50%;
			justify-content:space-between;
			.welcom_footer_dot{
				background-color:#abc2ce;
				border-radius:50%;
				height:6px;
				width:6px;
			}
			.is-visible{
				background-color:#7b94f9;
				animation-name:fadeIn;
				animation-duration:1s;
				animation-iteration-count: 1;

			}
		}
	}
}

.show-next-enter-active, .show-next-leave-active, .show-prev-enter-active, .show-prev-leave-active {
	transition:all 1s;

}

.show-next-enter, .show-prev-leave-to{
	opacity:0.8
}

.show-next-leave-to, .show-prev-enter{
	opacity:0.0
}

.header_img{
	animation-name:pictmove;
	animation-timing-function:linear;
	animation-iteration-count:infinite;
	animation-direction:alternate;
	animation-duration:31s;
}
@keyframes pictmove {
	0% {
		transform:translate(0,0px);
	}
	100%{
		transform:translate(0,-50%);
	}
}

</style>
