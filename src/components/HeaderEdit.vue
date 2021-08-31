<template>
	<div>
		<div class="headerpreview__wrapper">
			<div class="header_body">
				<transition-group :name="transition_name">
					<img v-for="(content,index) in dirInfo"
						v-show="isVisible(index)"
						:key="`image_${index}`"
						class="header_img"
						:class="isVisible(index)"
						:src="content.href">
				</transition-group>
			</div>
			<div class="welcom_title">
				<div class="title_text">
					{{ title }}
				</div>
			</div>
			<div class="welcom_footer">
				<div v-for="(content, index) in contents"
					:key="`dot_${index}`"
					class="welcom_footer_dot"
					:class="isVisible(index)" />
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
		</div>
	</div>
</template>
<script>
import Mymodules from '../js/modules'
import Actions from '@nextcloud/vue/dist/Components/Actions'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import DirList from './DirList.vue'
import Thums from './Thums.vue'
export default {
	name: 'HeaderEdit',
	components: {
		Actions,
		ActionButton,
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
			contents: [
				'http://localhost:8000/index.php/core/preview?fileId=83&x=2112&y=1320&a=true',
				'http://localhost:8000/index.php/s/mq9o37yNwnQssyY',
			],
			transition_name: 'show-next',
			visible_content: 0,
			selectedFile: null,
			dirInfo: [],
		}
	},
	mounted() {
		setInterval(() => {
			this.visible_content = (this.visible_content + 1) % (this.dirInfo.length)
		}, 4000)
		this.fetchDirInfo()

	},
	methods: {
		fetchDirInfo() {
			if (!this.user.id) { return }
			this.fileDir = `${this.user.id}/announce_${this.user.id}/headers`
			Mymodules.fetchDirInfoOrCreate(this.fileDir).then((dirInfo) => {
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
							console.info(result)

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
				shareId: this.user.shareId,
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
					}
				}).catch((e) => {
					console.info(e)
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
					console.info('postConfigErr')
					console.info(e)
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
		width:100%;
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

.header__form{
	padding:20px;
	input {
		width:400px;
	}
}

</style>
