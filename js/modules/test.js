// Test module to verify module loading
export function testFunction() {
    console.log('Test module loaded successfully');
    return 'test';
}

// Test class
export class TestClass {
    constructor() {
        console.log('Test class instantiated');
    }
    
    testMethod() {
        return 'test method called';
    }
} 