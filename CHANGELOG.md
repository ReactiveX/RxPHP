# 1.4.0

### Bug Fixes

- Fixed doc block, demo updates ([7090947](https://github.com/ReactiveX/RxPHP/commit/70909479b99f2cc8fafa3ca666ff842b9c9f013e)) ([306ead9](https://github.com/ReactiveX/RxPHP/commit/306ead9c10356f2efcb0711d7581d6ebca2efd25)) ([8c4e9ab](https://github.com/ReactiveX/RxPHP/commit/8c4e9abf2d0993dbe126182fb1a6289c4568d353)) ([a21b8fd](https://github.com/ReactiveX/RxPHP/commit/a21b8fdf28bbe4b95599fca6febf2361899454d6))
- Fixed `reduce` operator issue with falsy seed ([#71](https://github.com/ReactiveX/RxPHP/commit/d1cb412535beddb4d9892887104921340495bf81))
- Fixed skipped tests ([26c2476](https://github.com/ReactiveX/RxPHP/commit/26c2476a2459307e9883279a258e1ed6dc854ed2))
- Fixed `retryWhen` ([#59](https://github.com/ReactiveX/RxPHP/commit/e8e44ea9ae0b8f20c5fc4332aecec498cdcfc721))

### Features

- Added `flatMapTo` operator ([a8c6967](https://github.com/ReactiveX/RxPHP/commit/a8c69671ff4b7872423ebb602fba759c9564ae66))
- Added `pluck` operator ([ec1fce1](https://github.com/ReactiveX/RxPHP/commit/ec1fce117bdc9a82e004624e2e8fcfb20ed50add))
- Added `average` operator ([da591a6](https://github.com/ReactiveX/RxPHP/commit/da591a6cf8f32e923a597d08426fe1c8be116f7b))
- Added `sum` operator ([2f44168](https://github.com/ReactiveX/RxPHP/commit/2f441687b8b806f4151f8966f38f1b11b065cd77))
- Added CONTRIBUTING.md ([e45210c](https://github.com/ReactiveX/RxPHP/commit/e45210c9facbb3c38a48c8018f883dc820c6a292))
- Added `min` operator ([f458564](https://github.com/ReactiveX/RxPHP/commit/f458564c82245813e3cf4d7d84a461b8a983e270))
- Added `repeatWhen` operator ([d0fc1f8](https://github.com/ReactiveX/RxPHP/commit/d0fc1f84721940638274cd71492e91ea0030e4e4))
- Added `race` operator (amb) ([81b70e7](https://github.com/ReactiveX/RxPHP/commit/81b70e7e7eca111fdfaa0bdc00a5b45afd569a6b))
- Added `takeLast` operator ([8759ca4](https://github.com/ReactiveX/RxPHP/commit/8759ca42ba201b94c0a3dbe0979fde673c83df25))

# 1.3.0

### Bug Fixes

- Fixed `combineLatest` when using the EventLoopScheduler ([12fce12](https://github.com/ReactiveX/RxPHP/commit/12fce1200e8d7951bcfc12a681f7105d5a171620))
- Fixed argument ordering issue with `combineLatest` ([c5a8e5a](https://github.com/ReactiveX/RxPHP/commit/c5a8e5a07b4cae3bfe506ba2fced59fa3ed0467e))
- Fixed `shareReplay`, so arguments are optional ([e38e8a4](https://github.com/ReactiveX/RxPHP/commit/e38e8a4da42f154caa58e8aa006b7c515fe3d809))
- Fixed double subscription issue with `concatAll` ([4c64a82](https://github.com/ReactiveX/RxPHP/commit/4c64a82478fdf180dee97283413da85c38bc4ab7))
- Fixed `delay` now uses `materialize` and `timestamp`, so that it has consistent behavior between all supported schedulers ([#51](https://github.com/ReactiveX/RxPHP/pull/51)) 
- Fixed EventLoopScheduler, which is now based off of the VirtualTimeScheduler ([#50](https://github.com/ReactiveX/RxPHP/pull/50))


### Features

- Added `materialize` and `dematerialize` operators ([6d6bba4](https://github.com/ReactiveX/RxPHP/commit/6d6bba44a139bb4c6a05ec5b4521ac3d13825a24))
- Added `timestamp` operator ([4109934](https://github.com/ReactiveX/RxPHP/commit/41099345d05e2dac87b84ea3b297ab31421f9504)) 
- Added `switchLatest` operator ([58c95b0](https://github.com/ReactiveX/RxPHP/commit/58c95b04271dd3dee8f1c71673ba7e4b6056d8e5))
- Added `partition` operator ([ca95144](https://github.com/ReactiveX/RxPHP/commit/ca951446f38a0ae16bc02039f70c89c74c98fe66))
- Added `flatMapLatest` operator ([c0d15ff](https://github.com/ReactiveX/RxPHP/commit/c0d15ffd88ecda1a32f7cba73112c28c667ce9a8))

# 1.2.0

### Bug Fixes

- Fixed uninitialized disposable in `skipUntil` ([2b5ea0b](https://github.com/ReactiveX/RxPHP/commit/2b5ea0b5fc8ae6f6cda20eb0fb0a3512ea3ef217))
- Fixed `asObservable` operator was not passing through the scheduler ([d805cfd](https://github.com/ReactiveX/RxPHP/commit/d805cfdf620334ab977ef9532f1c2e29f79c4cf7))
- Fixed uninitialized disposable in `retry` ([ca4da7e](https://github.com/ReactiveX/RxPHP/commit/ca4da7e24527ffcb7d206865e1e8c2caee3ea583))
- Fixed #23 - changing yield behavior in hhvm ([da2737c](https://github.com/ReactiveX/RxPHP/commit/da2737ccd19fa3faed3cedc734130c8e6866d66f))
- Fixed #33 - For Rx\React\Promise::toObservable, wrap non-exceptions on reject ([ed852de](https://github.com/ReactiveX/RxPHP/commit/ed852de1596652466d72ef0a67b491a3b0242927))

### Features

- Added ability to record and validate output of demo files ([883ad4b](https://github.com/ReactiveX/RxPHP/commit/883ad4ba8bfeb3e2192d7501039b99eb8a641229))
- Added `catchError` operator ([cd4fc03](https://github.com/ReactiveX/RxPHP/commit/cd4fc034644e7d30c697597d9cec4d4995080dcd))
- Added `takeWhile` and `takeWhileWithIndex` operators ([177835e](https://github.com/ReactiveX/RxPHP/commit/177835e8dbfd593f1f62cefb88012bd9ed306d77))
- Added `startWith` and `startWithArray` operators ([585f893](https://github.com/ReactiveX/RxPHP/commit/585f8933dd859c720aacee385f3ac0c62f07f0f0))
- Added `retryWhen` operator ([19b36fc](https://github.com/ReactiveX/RxPHP/commit/19b36fc5f232ad1f4d8a7da953e59d09affc7bb1))
- Added utility to generate documentation for reactivex.io ([a62d46b](https://github.com/ReactiveX/RxPHP/commit/a62d46b50a24bc88adca5da64215f00c5ce2b350))
- Added `concatAll` and `concatMap` operators ([c794cf3](https://github.com/ReactiveX/RxPHP/commit/c794cf31cf577fe374f7ab120c6a0b20afeb3957))
- Added `skipWhile` and `skipWhileWithIndex` operators ([80c997f](https://github.com/ReactiveX/RxPHP/commit/80c997ff1cc2ed852ca2db4f60872fc32cab2daf))
- Added `max` operator ([1a841fa](https://github.com/ReactiveX/RxPHP/commit/1a841fa25e7b17c70fd00084a3688fb5d432ce15))

# 1.1.0

### Bug fixes

- Fixed bug where the `map` operator called `onNext` after it was disposed ([8a1d68c](https://github.com/ReactiveX/RxPHP/commit/8a1d68c))
- Minor fixes to `groupByUntil` ([f6f56e3](https://github.com/ReactiveX/RxPHP/commit/f6f56e3))
- Minor fixes to `delay` ([2613f36](https://github.com/ReactiveX/RxPHP/commit/2613f36))

### Features

- Added `range` observable ([1684522](https://github.com/ReactiveX/RxPHP/commit/1684522)) ([0ceab90](https://github.com/ReactiveX/RxPHP/commit/0ceab90)) ([121806c](https://github.com/ReactiveX/RxPHP/commit/121806c))
- Added index to `map` selector ([8a1d68c](https://github.com/ReactiveX/RxPHP/commit/8a1d68c))
- Added `mapTo` operator ([8a1d68c](https://github.com/ReactiveX/RxPHP/commit/8a1d68c))
- Added `timer` observable ([304bc0c](https://github.com/ReactiveX/RxPHP/commit/304bc0c))
- Added `distinct` and `distinctKey` ([e9575f1](https://github.com/ReactiveX/RxPHP/commit/e9575f1))
- Split `distinctUntilChanged` into `distinctUntilChanged` and `distinctUntilKeyChanged` ([e9575f1](https://github.com/ReactiveX/RxPHP/commit/e9575f1))
