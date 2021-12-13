import UpdateEvaluation from './updateEvaluation'
import Evaluation from './evaluation'
import EvaluationBudget from './evaluationBudget'
import ImportantFields from './importantFields'
import changeChecker from '../utils/form/changeChecker'
import '../utils/accordionChevron'
import  '../utils/maskNumber'
import AutoSizer from '../utils/form/autoSizer'
import StopWatch from '../utils/stopWatch'

document.addEventListener('DOMContentLoaded', () => {
    const stopWatch = new StopWatch()
    stopWatch.start()
    new UpdateEvaluation()
    new Evaluation()
    new EvaluationBudget()
    new ImportantFields()
    new changeChecker('evaluation')
    new AutoSizer('textarea')
    
    stopWatch.stop()
})