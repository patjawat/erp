
const inputs=document.querySelectorAll(".input");

function addCl() {
  let par=this.parentNode.parentNode;
  par.classList.add("focus");
}

function remC1() {
  let par=this.parentNode.parentNode;
  if(this.value=="")
  par.classList.remove("focus");
}

inputs.forEach(input=>{
  input.addEventListener("focus",addCl);
  input.addEventListener("blur",remC1);
})