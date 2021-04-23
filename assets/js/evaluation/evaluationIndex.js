import UpdateEvaluation from './updateEvaluation'
import Evaluation from './evaluation'
import changeChecker from '../utils/form/changeChecker'
import '../utils/accordionChevron'
import AutoSizer from '../utils/form/autoSizer'

document.addEventListener('DOMContentLoaded', () => {
    new UpdateEvaluation()
    new Evaluation()
    new changeChecker('evaluation')
    new AutoSizer('textarea')
})