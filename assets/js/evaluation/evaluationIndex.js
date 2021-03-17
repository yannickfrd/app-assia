import UpdateEvaluation from './updateEvaluation'
import Evaluation from './evaluation'
import CheckChange from '../utils/checkChange'
import '../utils/accordionChevron'
import AutoSize from '../utils/AutoSize'

document.addEventListener('DOMContentLoaded', () => {
    new UpdateEvaluation()
    new Evaluation()
    new CheckChange('evaluation')
    new AutoSize('textarea')
})