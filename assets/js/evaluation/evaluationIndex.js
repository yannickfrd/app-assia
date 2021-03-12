import Evaluation from './evaluation'
import UpdateEvaluation from './updateEvaluation'
import CheckChange from '../utils/checkChange'
import '../utils/accordionChevron'
import AutoSize from '../utils/AutoSize'

document.addEventListener('DOMContentLoaded', () => {
    new UpdateEvaluation()
    new Evaluation()
    new CheckChange('evaluation') // form name
    new AutoSize('textarea')
})