define([
    "ko",
	],function(ko){

    var window = this || (0, eval)('this'),
        document = window['document'],
        navigator = window['navigator'],
        jQueryInstance = window["jQuery"],
        JSON = window["JSON"];

    if (!jQueryInstance && typeof jQuery !== "undefined") {
        jQueryInstance = jQuery;
    }
    var MenuModelBinding = function(){
        var self = this;
        
        var boundElementDomDataKey = ko.utils.domData.nextKey();
        var contextSubscribable = ko.utils.createSymbolOrString('_subscribable');
        var contextAncestorBindingInfo = ko.utils.createSymbolOrString('_ancestorBindingInfo');
        var contextDataDependency = ko.utils.createSymbolOrString('_dataDependency');

        var bindingDoesNotRecurseIntoElementTypes = {
            // Don't want bindings that operate on text nodes to mutate <script> and <textarea> contents,
            // because it's unexpected and a potential XSS issue.
            // Also bindings should not operate on <template> elements since this breaks in Internet Explorer
            // and because such elements' contents are always intended to be bound in a different context
            // from where they appear in the document.
            'script': true,
            'textarea': true,
            'template': true
        };

        // Returns the valueAccessor function for a binding value
        function makeValueAccessor(value) {
            return function() {
                return value;
            };
        }

        // Returns the value of a valueAccessor function
        function evaluateValueAccessor(valueAccessor) {
            return valueAccessor();
        }

        // Given a function that returns bindings, create and return a new object that contains
        // binding value-accessors functions. Each accessor function calls the original function
        // so that it always gets the latest value and all dependencies are captured. This is used
        // by ko.applyBindingsToNode and getBindingsAndMakeAccessors.
        function makeAccessorsFromFunction(callback) {
            return ko.utils.objectMap(ko.dependencyDetection.ignore(callback), function(value, key) {
                return function() {
                    return callback()[key];
                };
            });
        }

        // Given a bindings function or object, create and return a new object that contains
        // binding value-accessors functions. This is used by ko.applyBindingsToNode.
        function makeBindingAccessors(bindings, context, node) {
            if (typeof bindings === 'function') {
                return makeAccessorsFromFunction(bindings.bind(null, context, node));
            } else {
                return ko.utils.objectMap(bindings, makeValueAccessor);
            }
        }

        // This function is used if the binding provider doesn't include a getBindingAccessors function.
        // It must be called with 'this' set to the provider instance.
        function getBindingsAndMakeAccessors(node, context) {
            return makeAccessorsFromFunction(this['getBindings'].bind(this, node, context));
        }

        function validateThatBindingIsAllowedForVirtualElements(bindingName) {
            var validator = ko.virtualElements.allowedBindings[bindingName];
            if (!validator)
                throw new Error("The binding '" + bindingName + "' cannot be used with virtual elements")
        }
        
        function getBindingContext(viewModelOrBindingContext, extendContextCallback) {
            return viewModelOrBindingContext && (viewModelOrBindingContext instanceof ko.bindingContext)
                ? viewModelOrBindingContext
                : new ko.bindingContext(viewModelOrBindingContext, undefined, undefined, extendContextCallback);
        }

        function applyBindingsToDescendantsInternal(bindingContext, elementOrVirtualElement) {
            var nextInQueue = ko.virtualElements.firstChild(elementOrVirtualElement);

            if (nextInQueue) {
                var currentChild,
                    provider = ko.bindingProvider['instance'],
                    preprocessNode = provider['preprocessNode'];

                // Preprocessing allows a binding provider to mutate a node before bindings are applied to it. For example it's
                // possible to insert new siblings after it, and/or replace the node with a different one. This can be used to
                // implement custom binding syntaxes, such as {{ value }} for string interpolation, or custom element types that
                // trigger insertion of <template> contents at that point in the document.
                if (preprocessNode) {
                    while (currentChild = nextInQueue) {
                        nextInQueue = ko.virtualElements.nextSibling(currentChild);
                        preprocessNode.call(provider, currentChild);
                    }
                    // Reset nextInQueue for the next loop
                    nextInQueue = ko.virtualElements.firstChild(elementOrVirtualElement);
                }

                while (currentChild = nextInQueue) {
                    // Keep a record of the next child *before* applying bindings, in case the binding removes the current child from its position
                    nextInQueue = ko.virtualElements.nextSibling(currentChild);
                    applyBindingsToNodeAndDescendantsInternal(bindingContext, currentChild);
                }
            }
            ko.bindingEvent.notify(elementOrVirtualElement, ko.bindingEvent.childrenComplete);
        }

        function applyBindingsToNodeAndDescendantsInternal(bindingContext, nodeVerified) {
            var bindingContextForDescendants = bindingContext;

            var isElement = (nodeVerified.nodeType === 1);
            if (isElement) // Workaround IE <= 8 HTML parsing weirdness
                ko.virtualElements.normaliseVirtualElementDomStructure(nodeVerified);

            // Perf optimisation: Apply bindings only if...
            // (1) We need to store the binding info for the node (all element nodes)
            // (2) It might have bindings (e.g., it has a data-bind attribute, or it's a marker for a containerless template)
            var shouldApplyBindings = isElement || ko.bindingProvider['instance']['nodeHasBindings'](nodeVerified);
            if (shouldApplyBindings)
                bindingContextForDescendants = self.applyBindingsToNodeInternal(nodeVerified, null, bindingContext)['bindingContextForDescendants'];

            if (bindingContextForDescendants && !bindingDoesNotRecurseIntoElementTypes[ko.utils.tagNameLower(nodeVerified)]) {
                applyBindingsToDescendantsInternal(bindingContextForDescendants, nodeVerified);
            }
        }

        function topologicalSortBindings(bindings) {
            // Depth-first sort
            var result = [],                // The list of key/handler pairs that we will return
                bindingsConsidered = {},    // A temporary record of which bindings are already in 'result'
                cyclicDependencyStack = []; // Keeps track of a depth-search so that, if there's a cycle, we know which bindings caused it
            ko.utils.objectForEach(bindings, function pushBinding(bindingKey) {
                if (!bindingsConsidered[bindingKey]) {
                    var binding = ko['getBindingHandler'](bindingKey);
                    if (binding) {
                        // First add dependencies (if any) of the current binding
                        if (binding['after']) {
                            cyclicDependencyStack.push(bindingKey);
                            ko.utils.arrayForEach(binding['after'], function(bindingDependencyKey) {
                                if (bindings[bindingDependencyKey]) {
                                    if (ko.utils.arrayIndexOf(cyclicDependencyStack, bindingDependencyKey) !== -1) {
                                        throw Error("Cannot combine the following bindings, because they have a cyclic dependency: " + cyclicDependencyStack.join(", "));
                                    } else {
                                        pushBinding(bindingDependencyKey);
                                    }
                                }
                            });
                            cyclicDependencyStack.length--;
                        }
                        // Next add the current binding
                        result.push({ key: bindingKey, handler: binding });
                    }
                    bindingsConsidered[bindingKey] = true;
                }
            });

            return result;
        }

        self.applyBindings = function (viewModelOrBindingContext, rootNode, extendContextCallback) {
            // If jQuery is loaded after Knockout, we won't initially have access to it. So save it here.
            if (!jQueryInstance && window['jQuery']) {
                jQueryInstance = window['jQuery'];
            }

            if (arguments.length < 2) {
                rootNode = document.body;
                if (!rootNode) {
                    throw Error("ko.applyBindings: could not find document.body; has the document been loaded?");
                }
            } else if (!rootNode || (rootNode.nodeType !== 1 && rootNode.nodeType !== 8)) {
                throw Error("ko.applyBindings: first parameter should be your view model; second parameter should be a DOM node");
            }

            applyBindingsToNodeAndDescendantsInternal(getBindingContext(viewModelOrBindingContext, extendContextCallback), rootNode);
        };

        self.applyBindingsToNodeInternal = function(node, sourceBindings, bindingContext) {
            var bindingInfo = ko.utils.domData.getOrSet(node, boundElementDomDataKey, {});

            // Prevent multiple applyBindings calls for the same node, except when a binding value is specified
            var alreadyBound = bindingInfo.alreadyBound;
            if (!sourceBindings) {
                if (alreadyBound) {
                    //throw Error("You cannot apply bindings multiple times to the same element.");
                }
                bindingInfo.alreadyBound = true;
            }
            if (!alreadyBound) {
                bindingInfo.context = bindingContext;
            }
            if (!bindingInfo.notifiedEvents) {
                bindingInfo.notifiedEvents = {};
            }

            // Use bindings if given, otherwise fall back on asking the bindings provider to give us some bindings
            var bindings;
            if (sourceBindings && typeof sourceBindings !== 'function') {
                bindings = sourceBindings;
            } else {
                var provider = ko.bindingProvider['instance'],
                    getBindings = provider['getBindingAccessors'] || getBindingsAndMakeAccessors;

                // Get the binding from the provider within a computed observable so that we can update the bindings whenever
                // the binding context is updated or if the binding provider accesses observables.
                var bindingsUpdater = ko.dependentObservable(
                    function() {
                        bindings = sourceBindings ? sourceBindings(bindingContext, node) : getBindings.call(provider, node, bindingContext);
                        // Register a dependency on the binding context to support observable view models.
                        if (bindings) {
                            if (bindingContext[contextSubscribable]) {
                                bindingContext[contextSubscribable]();
                            }
                            if (bindingContext[contextDataDependency]) {
                                bindingContext[contextDataDependency]();
                            }
                        }
                        return bindings;
                    },
                    null, { disposeWhenNodeIsRemoved: node }
                );

                if (!bindings || !bindingsUpdater.isActive())
                    bindingsUpdater = null;
            }

            var contextToExtend = bindingContext;
            var bindingHandlerThatControlsDescendantBindings;
            if (bindings) {
                // Return the value accessor for a given binding. When bindings are static (won't be updated because of a binding
                // context update), just return the value accessor from the binding. Otherwise, return a function that always gets
                // the latest binding value and registers a dependency on the binding updater.
                var getValueAccessor = bindingsUpdater
                    ? function(bindingKey) {
                        return function() {
                            return evaluateValueAccessor(bindingsUpdater()[bindingKey]);
                        };
                    } : function(bindingKey) {
                        return bindings[bindingKey];
                    };

                // Use of allBindings as a function is maintained for backwards compatibility, but its use is deprecated
                function allBindings() {
                    return ko.utils.objectMap(bindingsUpdater ? bindingsUpdater() : bindings, evaluateValueAccessor);
                }
                // The following is the 3.x allBindings API
                allBindings['get'] = function(key) {
                    return bindings[key] && evaluateValueAccessor(getValueAccessor(key));
                };
                allBindings['has'] = function(key) {
                    return key in bindings;
                };

                if (ko.bindingEvent.childrenComplete in bindings) {
                    ko.bindingEvent.subscribe(node, ko.bindingEvent.childrenComplete, function () {
                        var callback = evaluateValueAccessor(bindings[ko.bindingEvent.childrenComplete]);
                        if (callback) {
                            var nodes = ko.virtualElements.childNodes(node);
                            if (nodes.length) {
                                callback(nodes, ko.dataFor(nodes[0]));
                            }
                        }
                    });
                }

                if (ko.bindingEvent.descendantsComplete in bindings) {
                    contextToExtend = ko.bindingEvent.startPossiblyAsyncContentBinding(node, bindingContext);
                    ko.bindingEvent.subscribe(node, ko.bindingEvent.descendantsComplete, function () {
                        var callback = evaluateValueAccessor(bindings[ko.bindingEvent.descendantsComplete]);
                        if (callback && ko.virtualElements.firstChild(node)) {
                            callback(node);
                        }
                    });
                }

                // First put the bindings into the right order
                var orderedBindings = topologicalSortBindings(bindings);

                // Go through the sorted bindings, calling init and update for each
                ko.utils.arrayForEach(orderedBindings, function(bindingKeyAndHandler) {
                    // Note that topologicalSortBindings has already filtered out any nonexistent binding handlers,
                    // so bindingKeyAndHandler.handler will always be nonnull.
                    var handlerInitFn = bindingKeyAndHandler.handler["init"],
                        handlerUpdateFn = bindingKeyAndHandler.handler["update"],
                        bindingKey = bindingKeyAndHandler.key;

                    if (node.nodeType === 8) {
                        validateThatBindingIsAllowedForVirtualElements(bindingKey);
                    }

                    try {
                        // Run init, ignoring any dependencies
                        if (typeof handlerInitFn == "function") {
                            ko.dependencyDetection.ignore(function() {
                                var initResult = handlerInitFn(node, getValueAccessor(bindingKey), allBindings, contextToExtend['$data'], contextToExtend);

                                // If this binding handler claims to control descendant bindings, make a note of this
                                if (initResult && initResult['controlsDescendantBindings']) {
                                    if (bindingHandlerThatControlsDescendantBindings !== undefined)
                                        throw new Error("Multiple bindings (" + bindingHandlerThatControlsDescendantBindings + " and " + bindingKey + ") are trying to control descendant bindings of the same element. You cannot use these bindings together on the same element.");
                                    bindingHandlerThatControlsDescendantBindings = bindingKey;
                                }
                            });
                        }

                        // Run update in its own computed wrapper
                        if (typeof handlerUpdateFn == "function") {
                            ko.dependentObservable(
                                function() {
                                    handlerUpdateFn(node, getValueAccessor(bindingKey), allBindings, contextToExtend['$data'], contextToExtend);
                                },
                                null,
                                { disposeWhenNodeIsRemoved: node }
                            );
                        }
                    } catch (ex) {
                        ex.message = "Unable to process binding \"" + bindingKey + ": " + bindings[bindingKey] + "\"\nMessage: " + ex.message;
                        throw ex;
                    }
                });
            }

            var shouldBindDescendants = bindingHandlerThatControlsDescendantBindings === undefined;
            return {
                'shouldBindDescendants': shouldBindDescendants,
                'bindingContextForDescendants': shouldBindDescendants && contextToExtend
            };
        };
    }
    return MenuModelBinding;
});