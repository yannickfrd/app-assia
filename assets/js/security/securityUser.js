// import Username from './username'
import Username from './username'
import SeePassword from './seePassword'
import changeChecker from '../utils/form/changeChecker'
import '../utils/maskPhone'
import SelectManager from '../utils/form/SelectManager'
import ServiceUserManager from "./ServiceUserManager";

document.addEventListener('DOMContentLoaded', () => {
    new ServiceUserManager()
    new Username('user')
    new SeePassword()
    new changeChecker('user') // form name
    new SelectManager('#user_roles')
})