
document.querySelectorAll('input[data-group-target]').forEach(inputElt => {
    inputElt.addEventListener('click', e => {
        const targetElt = document.querySelector(`[data-group-name="${e.currentTarget.dataset.groupTarget}"]`)
        const isChecked = inputElt.checked

        targetElt.querySelectorAll('input[type="checkbox"]').forEach(checkboxElt => {
            checkboxElt.checked = isChecked
        })
    })
})