<template>
	<div class="loader__wrapper">
		<section class="loader__container">
			<div class="loader" />
		</section>
	</div>
</template>
<script>
export default {
	name: 'Loader',
}
</script>
<style scoped>
.loader__wrapper{
	display:flex;
	flex-wrap:wrap;
	justify-content:center;
}

.loader__container{
	margin:2%;
	display:inline-flex;
	justify-content:center;
	align-items:center;
	width:29%;
	min-width:200px;
	min-height:200px;
}

.loader{
	width:40px;
	height:40px;
	border-radius:50%;
	border:solid 4px;
	border-color:#000000 #00000010 #00000010 #00000010;
	position:relative;
	animation-name:spin;
	animation-duration:1s;
	animation-iteration-count:infinite;
	animation-timing-function:linear;

}
@keyframes spin {
	to {
		transform: rotate(360deg);
	}
}

</style>
