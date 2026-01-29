import React from "react";
import  {DataProvider}  from '../src/LogicComp/DataProvider';
import {BrowserRouter} from "react-router-dom";
import AppRouter from "./LogicComp/AppRouter";


const App = () => {
  return (
    <DataProvider>
      <BrowserRouter>
        <AppRouter/>
      </BrowserRouter>
    </DataProvider>
  )
};

export default App;
