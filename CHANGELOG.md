# 2.0.7

### Update

- Forward compatibility with react/event-loop 1.0 and 0.5 while still supporting 0.4 ([5f91a62](https://github.com/ReactiveX/RxPHP/commit/5f91a6200ecda70776d78c969f4d04294fe5ac89))

# 2.0.6

### Bug Fixes

- Reroute exceptions in onNext to onError by default ([b7ba556](https://github.com/ReactiveX/RxPHP/commit/b7ba55623676257cf4b1bea5d8a9218333cfb563))

# 2.0.5

### Bug Fixes

- Immediately complete if the iterator is no longer valid ([af6765a](https://github.com/ReactiveX/RxPHP/commit/af6765a0f996b2f8d1d4b8d93e1d0bb755587843))
- Dispose inner observable when promise is cancelled ([4855812](https://github.com/ReactiveX/RxPHP/commit/48558124af3a47c2057ce50953bf5d7019bd6382))

# 2.0.4

### Bug Fixes

- Fixed issue where EventLoopScheduler did not cancel timers in certain circumstances ([c915843](https://github.com/ReactiveX/RxPHP/commit/c91584391da87f78649af0222368f14ba22e0837))

### Features

- Added `singleInstance` operator ([4f77d38](https://github.com/ReactiveX/RxPHP/pull/185/commits/4f77d386b4596eccb2f5a3e9e3f9658a9aa57015))

# 2.0.3

### Bug Fixes

- Fixed order of subscriptions in `takeUntil` ([1838ede](https://github.com/ReactiveX/RxPHP/commit/1838ede703bcb14eb67ed54cd61401f2ffff08c8))
- Fixed issue where disposed of scheduled item would not cancel timer in some instances ([bb2c5a0](https://github.com/ReactiveX/RxPHP/commit/bb2c5a09dd37d0a4f5dd16c8c5ae8607e59936cb))
- Removed throttle.expect to get rid of non-determinate test failures ([97a980c](https://github.com/ReactiveX/RxPHP/commit/97a980c3b87dae992ac774c03d87726a268a042a))

### Features

- Updated PHPUnit to 5.7 ([b1b37ab](https://github.com/ReactiveX/RxPHP/commit/b1b37ab2cf4ae3649b8571b0d42d7fcfefc079ae))

# 2.0.2

### Bug Fixes

- Make scheduler optional for `ReplaySubject` ([a8d1c50](https://github.com/ReactiveX/RxPHP/commit/a8d1c50d69c80ae71a945886de06a01d1b0f4d09))
- EventLoop will only ever scheduler 1 timer now #167 #165 ([bccbce9](https://github.com/ReactiveX/RxPHP/commit/bccbce9af3dc9bf083fb26b5812aad119ad2e97b))
- Canceled items are now removed from `PriorityQueue` #168 ([6a4f9e7](https://github.com/ReactiveX/RxPHP/pull/168/commits/6a4f9e7fd8b3f9390b19bd451b87c5907b012562))
- Fix memory leak and refactor `groupBy*` operators ([7e4dc3c](https://github.com/ReactiveX/RxPHP/commit/7e4dc3c9e4eb04045bcb94db3296cb452def2cda))

### Features

- Added `withLatestFrom` operator ([273df81](https://github.com/ReactiveX/RxPHP/commit/273df812f7871a320b6702bf8b74657423fafa9c))


# 2.0.1

### Bug Fixes

- Fix `concat` to dispose of current observable ([1eadb7b](https://github.com/ReactiveX/RxPHP/commit/1eadb7be7e026a07331f12d585d47e86cbd818bb))

# 2.0.0

### Changes and Additions
- Added global static `Scheduler` class that allows setting scheduler factory callables
- Schedulers are now passed in during `Observable` construction or into operators as needed
- Static Observable constructors and operators will get scheduler from global static `Scheduler` if not specified
- PHP 7 is required
- HHVM support was removed
- All files now `declare(strict_types=1)`
- `ObserverInterface::onError` now takes a `Throwable` parameter instead of `Exception`
- `subscribeCallback` has been deprecated in favor of `subscribe` which now takes callables or an `ObserverInterface` implementation
- `doOnNext` and `doOnEach` have been deprecated in favor of `do` and follows the same syntax as `subscribe`
- `catchError` has been deprecated in favor of `catch`
- `just` has been deprecated in favor of `of`
- `emptyObservable` has been deprecated in favor of `empty`
- `switchLatest` has been deprecated in favor of `switch`
- `Observable` is now abstract and requires subclasses to define `_subscribe`
- Added `toPromise` and `fromPromise` operators
- Marble tests are now supported
- `timeout` now throws `TimeoutException` to allow detection of timeouts down stream
- Parameter and return types have been added

# 1.5.3

### Features

- Added `compose` operator ([140e21a](https://github.com/ReactiveX/RxPHP/commit/140e21a61c2bcd6e2572d3b6d51d3309934b29d1))
- Added plucking for multiple items at once ([11b86c9](https://github.com/ReactiveX/RxPHP/commit/11b86c9eccc2dfb7d767b7cc4986d4f2d4ff548e))
- Added custom operators in nested namespace ([897b747](https://github.com/ReactiveX/RxPHP/commit/897b74795d42b94fef3242ca9534345fcb45ed7e))

# 1.5.2

### Bug Fixes

- Fix interface mismatch on subscribe type hints ([b817619](https://github.com/ReactiveX/RxPHP/commit/b8176196a9bb836579838966b0b89dcfcbc48dd1))
- Fix `IteratorObservable` to check if the key is valid instead of null ([dafb14b](https://github.com/ReactiveX/RxPHP/commit/dafb14bc8f0bd22550325419f7ab2e98a454659b))

### Features

- Optimized `distinct` operator ([462d433](https://github.com/ReactiveX/RxPHP/commit/462d433a9268d1de60e7fcd1a9af19a2cac5f164))
- Added `finally` operator ([e2cfdb2](https://github.com/ReactiveX/RxPHP/commit/e2cfdb2f8b374b0687d64c9e774a8557dbe77b5c))
- Added `isEmpty` operator ([2429fb7](https://github.com/ReactiveX/RxPHP/commit/2429fb719de6c499db5da6cede086725bf82ece9))
- Added `forkJoin` operator ([9fb9197](https://github.com/ReactiveX/RxPHP/commit/9fb9197d04e47cd68363c0d4de845519ae2e2a66))
- Refactored `mergeAll` to be consistent with RxJS and `switchLatest` ([7aeb8ce](https://github.com/ReactiveX/RxPHP/commit/7aeb8cef74b29bdaa09ea969daf78577299a2aad))

# 1.5.1

### Bug Fixes

- Fixed non-strict search in CompositeDisposable ([c17fb6c](https://github.com/ReactiveX/RxPHP/commit/c17fb6cbba2ab3ac7c351b0e7c7e74f30b24f3b4))
- Fixed missing array type hint on Observable::__call ([1672dc1](https://github.com/ReactiveX/RxPHP/commit/1672dc12a3817888dbac8207a324cf14600f6dee))


# 1.5.0

### Bug Fixes

- Fixed throttle.php demo ([cee42e2](https://github.com/ReactiveX/RxPHP/commit/cee42e2f04dd1df23c4a5ac013cb017d0af79b78))
- Fixed Promise::fromObservable() to allow selecting the scheduler ([de88548](https://github.com/ReactiveX/RxPHP/commit/de8854883d8f47d251961503daf6beea1d492959))
- Fixed VirtualTimeScheduler to use now() method ([d5afdde](https://github.com/ReactiveX/RxPHP/commit/d5afdde8e1913f5e0edee6a9667edfefc49a90ba))
- Fixed `defer` swallowing errors ([#85](https://github.com/ReactiveX/RxPHP/commit/5332561fe773e61da35adfe92c9766f1594442bb))
- Fixed phpunit deprecation warnings ([b7f754c](https://github.com/ReactiveX/RxPHP/commit/b7f754c1d5f337ae3a6383316cd3ca4bcec085e9))
- Fixed scheduler disposable on delay dispose ([#87](https://github.com/ReactiveX/RxPHP/commit/13287702407b77222a5aa0f2599df55c0b4e24a5))

### Features

- Added custom operators ([c7d351d](https://github.com/ReactiveX/RxPHP/commit/c7d351d579f90134eef3701d87eb1ea8cd1e072e))
- Added output values for failed demos in tests ([c2b4a56](https://github.com/ReactiveX/RxPHP/commit/c2b4a561224b9c8e3c2690b1b71c8aa97a2b7d22))
- Added `throttle` operator ([b93d296](https://github.com/ReactiveX/RxPHP/commit/b93d296771a199c81e8ff8f21085f73d404c5ad9))
- Added support for cancellable promises ([a5602ab](https://github.com/ReactiveX/RxPHP/commit/a5602abfc5b27152a509effdaff635e1d1a97419))
- Added `switchFirst` operator ([620c70f](https://github.com/ReactiveX/RxPHP/commit/620c70f1dd9ebc8bff15a5cd889e84f314437c69)) ([a98c65f](https://github.com/ReactiveX/RxPHP/commit/a98c65f7f73802503939520852747a004f988aa1)) ([fc039fb](https://github.com/ReactiveX/RxPHP/commit/fc039fbf45ac8104b8f71aa0300aeb5e18beba5c)) ([b254d06](https://github.com/ReactiveX/RxPHP/commit/b254d060b6eb80824b0f299d4aacab02f6550ef9)) ([1f13650](https://github.com/ReactiveX/RxPHP/commit/1f136501c1491a9caffade898d099a152d0b3784))
- Added `DoObserver` ([4807ab1](https://github.com/ReactiveX/RxPHP/commit/4807ab11285bb3f5e665cff2ead766d72f775a87))
- Added coveralls ([7ed1a86](https://github.com/ReactiveX/RxPHP/commit/7ed1a860546c3b9748ae82e59e5bfa6053f3a95f))

# 1.4.1

### Bug Fixes

- Fixed EventLoopScheduler ([f0302d2](https://github.com/ReactiveX/RxPHP/commit/680eed0af8596a938871aac967419c150f0302d2))


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
