<template>
	<div class="thums__wrapper">
		<div v-for="file,index of files" :key="`thum_${index}`" class="thums__images">
			<img :src="file.href">
		</div>
	</div>
</template>
<script>
export default {
	name: 'Thums',
	props: {
		files: {
			type: Array,
			default: () => { return [] },
		},
	},
}
</script>
<style scoped>
.thums__wrapper{
	display:flex;
	width:100%;
	flex-wrap:wrap;
}

.thums__images{
	padding:2px;
	margin:2px;
	border-style:solid;
	border-color:lightslategrey;
	border-width:1px;

}

.thums__images img{
	width: 150px;
	height:auto;
}
</style>
