<template>
	<div>
		<div class="headerpreview__wrapper">
			<div class="header_body">
				<transition-group :name="transition_name">
					<img v-for="(content,index) in dirInfo"
						:key="`image_${index}`"
						class="header_img"
						:class="isVisible(index)"
						:src="content.href">
				</transition-group>
			</div>
			<div class="welcom_title">
				<div class="titles">
					<div class="title_text" :style="{'color':color}">
						{{ title }}
					</div>
					<div class="subtitle_text" :style="{'color':color}">
						{{ subTitle }}
					</div>
					<div class="message_text">
						{{ messageTxt }}
					</div>
				</div>
			</div>
			<div class="welcom_footer">
				<div class="welcom_footer_dot_wrapper">
					<div v-for="(content, index) in dirInfo"
						:key="`dot_${index}`"
						class="welcom_footer_dot"
						:class="isVisible(index)" />
				</div>
			</div>
		</div>
		<div class="header__form">
			<div class="input__label">
				タイトル
			</div>

			<input
				ref="title"
				v-model="title"
				class="titleinput"
				type="text">
			<div class="input__label">
				サブタイトル
			</div>

			<input
				ref="subtitle"
				v-model="subTitle"
				class="titleinput"
				type="text">
			<div class="input__label">
				文字色
			</div>
			<ColorPicker v-model="color">
				<button :style="{'background-color':color}">
					カラーを選択
				</button>
			</ColorPicker>
			<div class="input__label">
				メッセージ
			</div>

			<input
				ref="messagetxt"
				v-model="messageTxt"
				class="titleinput"
				type="text">
			<div class="input__label">
				ファイル
			</div>
			<div>
				<div class="fixed__row">
					<input
						id="fileinput"
						ref="fileinput"
						class="titleinput"
						type="file"
						@change="fileselect">
					<Actions>
						<ActionButton icon="icon-upload" @click="uploadFile">
							アップロード
						</ActionButton>
					</Actions>
				</div>
				<DirList :items.sync="dirInfo"
					:user-id="user.id" />
				<Thums :files="dirInfo" />
			</div>
			<div>
				<input type="button" value="保存" @click="saveConfig">
			</div>
			{{ headerConfig }}
		</div>
	</div>
</template>
<script>
import Mymodules from '../js/modules'
import Actions from '@nextcloud/vue/dist/Components/Actions'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import ColorPicker from '@nextcloud/vue/dist/Components/ColorPicker'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showSuccess } from '@nextcloud/dialogs'
import DirList from './DirList.vue'
import Thums from './Thums.vue'
export default {
	name: 'HeaderEdit',
	components: {
		Actions,
		ActionButton,
		ColorPicker,
		 DirList,
		Thums,

	},
	props: {
		user: {
			type: Object,
			default: () => { return {} },
		},
		headerConfig: {
			type: Object,
			default: () => { return {} },
		},
	},
	data() {
		return {
			img: '',
			title: 'タイトル',
			subTitle: 'サブタイトル',
			messageTxt: 'メッセージテキスト',
			color: '',
			transition_name: 'show-next',
			visible_content: 0,
			selectedFile: null,
			dirInfo: [],
			shareId: 0,
		}
	},
	mounted() {
		this.fetchDirInfo()
		setInterval(() => {
			if (this.dirInfo.length > 1) {
				this.visible_content = (this.visible_content + 1) % (this.dirInfo.length)
			}
		}, 10000)
		if (this.headerConfig) {
			this.title = this.headerConfig.value.title
			this.subTitle = this.headerConfig.value.subTitle
			this.messageTxt = this.headerConfig.value.messageTxt
			this.color = this.headerConfig.value.color
		}

	},
	methods: {
		fetchDirInfo() {
			if (!this.user.id) { return }
			const path = `/.announce_${this.user.id}_headers`
			this.fileDir = `${this.user.id}${path}`
			Mymodules.fetchDirInfoOrCreate(this.fileDir).then((dirInfo) => {
				Mymodules.shareDirToGroup(path, 'all_users').then((shareId) => {
					this.shareId = shareId
				})
				const regex = /image/
				this.dirInfo = dirInfo.filter((elm) => regex.test(elm.filetype))
			})

		},
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
		fileselect(e) {
			this.selectedFile = e.target?.files[0]
		},
		uploadFile() {
			if (this.selectedFile) {
				if (this.user.id && this.fileDir) {
					axios.put(`/remote.php/dav/files/${this.fileDir}/${this.selectedFile.name}`, this.selectedFile).then((result) => {
						if (result.status === 201 || result.status === 204) {
							this.fetchDirInfo()
						} else {
							// console.info(result)

						}

					}).catch((e) => {
						alert('error')
						console.error(e)
					})

				}
			}

		},
		validJson(s) {

			// preserve newlines, etc - use valid JSON
			s = s.replace(/\\n/g, '\\n')
				.replace(/\\'/g, "\\'")
				.replace(/\\"/g, '\\"')
				.replace(/\\&/g, '\\&')
				.replace(/\\r/g, '\\r')
				.replace(/\\t/g, '\\t')
				.replace(/\\b/g, '\\b')
				.replace(/\\f/g, '\\f')

			// remove non-printable and other non-valid JSON chars
			// s = s.replace(/[\u0000-\u0019]+/g, '')
			return s
		},
		saveConfig() {
			const value = {
				title: this.title,
				subTitle: this.subTitle,
				messageTxt: this.messageTxt,
				color: this.color,
				shareId: this.shareId,
			}
			const data = {
				kind: 'header',
				key: 'JSON',
				value: JSON.stringify(value),

			}
			if (this.headerConfig && this.headerConfig.id) {
				data.id = this.headerConfig.id
				axios.put(generateUrl(`/apps/welcomapp/config/${data.id}`), data).then((result) => {
					if (result.data) {
						const tmpData = result.data
						if (tmpData.value) {

							const s = this.validJson(tmpData.value)
							// preserve newlines, etc - use valid JSON
							tmpData.value = JSON.parse(s)
						}
						this.$emit('update:headerConfig', tmpData)
						showSuccess(t('welcomapp', 'save Header'))
					}
				}).catch((e) => {
					// console.info(e)
				})
			} else {

				axios.post(generateUrl('/apps/welcomapp/config'), data).then((result) => {
					if (result.data) {
						const tmpData = result.data
						if (tmpData.value) {
							const s = this.validJson(tmpData.value)
							// preserve newlines, etc - use valid JSON
							tmpData.value = JSON.parse(s)
						}
						this.$emit('update:headerConfig', tmpData)

					}

				}).catch((e) => {
					// console.info('postConfigErr')
					// console.info(e)
				})
			}

		},
	},
}
</script>
<style lang="scss">
.headerpreview__wrapper{
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
			object-fit: cover;
			opacity: 0.0;

		}
		.is-visible{
			opacity: 1.0;
		}
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
			}
		}
	}
}

.show-next-enter-active, .show-next-leave-active, .show-prev-enter-active, .show-prev-leave-active {
	transition:all 1s;

}

.header__form{
	padding:20px;
	input {
		width:400px;
	}
}

.header_img{
	animation-name:pictmove;
	animation-timing-function:linear;
	animation-iteration-count:infinite;
	animation-direction:alternate;
	animation-duration:20s;
}
@keyframes pictmove {
	0% {
		transform:translate(0,0px);
	}
	100%{
		transform:translate(0,-50%);
	}
}
@keyframes fadeIn {
	0%{
		opacity: 0.0;
	}
	100%{
		opacity:1.0;
	}
}

</style>
