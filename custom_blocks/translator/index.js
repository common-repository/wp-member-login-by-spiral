
import en from './lang/en.json'
import ja from './lang/ja.json'

export default function tranlate( keyword ) {

    if (typeof keyword === 'string' && keyword.length === 0) {
        return '';
    }

    let local = document.getElementsByTagName('html')[0].getAttribute('lang');
    
    if (local == "en-US") {
        return en[keyword];
    } else {
        return ja[keyword];
    }
}

