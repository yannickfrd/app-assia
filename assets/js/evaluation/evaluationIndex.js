import UpdateEvaluation from './updateEvaluation'
import Evaluation from './evaluation'
import EvaluationBudget from './evaluationBudget'
import ImportantFieldsChecker from '../utils/form/ImportantFieldsChecker'
import TwinFieldsChecker from './TwinFieldsChecker'
import changeChecker from '../utils/form/changeChecker'
import  '../utils/maskNumber'
import AutoSizer from '../utils/form/autoSizer'
import StopWatch from '../utils/stopWatch'
import CountryManager from './CountryManager'
import UpperCaseAfterDot from '../utils/form/UpperCaseAfterDot'

document.addEventListener('DOMContentLoaded', () => {
    const stopWatch = new StopWatch()
    stopWatch.start()
    new UpdateEvaluation()
    new Evaluation()
    new ImportantFieldsChecker('section.accordion') // A instancier après  Evaluation
    new TwinFieldsChecker()
    new EvaluationBudget()
    new CountryManager()
    new changeChecker('evaluation')
    new AutoSizer('textarea')
    new UpperCaseAfterDot('textarea')    
    stopWatch.stop()
})